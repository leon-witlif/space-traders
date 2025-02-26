<?php

declare(strict_types=1);

namespace App\Navigation;

use App\Helper\Navigation;
use App\SpaceTrader\Struct\System;
use App\SpaceTrader\Struct\SystemWaypoint;
use App\SpaceTrader\SystemApi;
use App\Storage\WaypointStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;

class Navigator extends AbstractController
{
    private ?System $system;
    /** @var array<string> */
    private array $fuelWaypoints;

    public function __construct(
        private readonly SystemApi $systemApi,
        private readonly WaypointStorage $waypointStorage,
    ) {
        $this->system = null;

        $fuelWaypoints = array_values(array_filter($this->waypointStorage->list(), fn (array $waypoint) => in_array('FUEL', $waypoint['exchange'])));
        $this->fuelWaypoints = array_map(fn (array $waypoint) => $waypoint['waypointSymbol'], $fuelWaypoints);
    }

    public function getSystem(): ?System
    {
        return $this->system;
    }

    public function initializeSystem(string $systemSymbol): void
    {
        $this->system = $this->systemApi->get($systemSymbol);

        foreach ($this->system->waypoints as $waypoint) {
            if (!$this->waypointStorage->get($waypoint->symbol)) {
                $this->waypointStorage->add(['waypointSymbol' => $waypoint->symbol, 'scanned' => false]);
            }
        }
    }

    public function scanWaypoint(): void
    {
        foreach ($this->waypointStorage->list() as $waypoint) {
            if (!$waypoint['scanned']) {
                $waypointResponse = $this->systemApi->waypoint(Navigation::getSystem($waypoint['waypointSymbol']), Navigation::getWaypoint($waypoint['waypointSymbol']));

                $data = [
                    'waypointSymbol' => $waypoint['waypointSymbol'],
                    'scanned' => true,
                    'type' => $waypointResponse->type,
                    'x' => $waypointResponse->x,
                    'y' => $waypointResponse->y,
                    'traits' => array_map(fn (array $trait) => $trait['symbol'], $waypointResponse->traits),
                    'factionSymbol' => $waypointResponse->faction?->symbol,
                ];

                try {
                    $marketResponse = $this->systemApi->market(Navigation::getSystem($waypoint['waypointSymbol']), Navigation::getWaypoint($waypoint['waypointSymbol']));

                    $data['exports'] = array_map(fn (array $tradeGood) => $tradeGood['symbol'], $marketResponse->exports);
                    $data['exchange'] = array_map(fn (array $tradeGood) => $tradeGood['symbol'], $marketResponse->exchange);
                } catch (ClientException) {
                    $data['exports'] = [];
                    $data['exchange'] = [];
                }

                $this->waypointStorage->update($this->waypointStorage->key($waypoint['waypointSymbol']), $data);

                break;
            }
        }
    }

    /**
     * @return array{from: SystemWaypoint, to: SystemWaypoint, route: array<SystemWaypoint>}
     */
    public function calculateRoute(SystemWaypoint $from, SystemWaypoint $to): array
    {
        return [
            'from' => $from,
            'to' => $to,
            'route' => $this->findPath($from, $to, [$this, 'distanceBetweenWaypoints']),
        ];
    }

    /**
     * @return array<SystemWaypoint>
     */
    private function findPath(SystemWaypoint $start, SystemWaypoint $goal, callable $heuristic): array
    {
        $openSet = [$start];
        $cameFrom = [];

        $gScore = [];
        foreach ($this->system->waypoints as $waypoint) {
            $gScore[$waypoint->symbol] = PHP_FLOAT_MAX;
        }
        $gScore[$start->symbol] = 0;

        $fScore = [];
        foreach ($this->system->waypoints as $waypoint) {
            $fScore[$waypoint->symbol] = PHP_FLOAT_MAX;
        }
        $fScore[$start->symbol] = $heuristic($start, $goal);

        do {
            $current = $this->lowestScoreWaypoint($openSet, $fScore);
            if ($current->symbol === $goal->symbol) {
                return $this->reconstructPath($cameFrom, $current);
            }

            $currentKey = array_find_key($openSet, fn (SystemWaypoint $waypoint) => $waypoint->symbol === $current->symbol);
            array_splice($openSet, $currentKey, 1);
            $openSet = array_values($openSet);

            foreach ($this->findNeighboringWaypoints($current, 200.0) as $neighbor) {
                $tentativeGScore = $gScore[$current->symbol] + $this->addWeight($this->distanceBetweenWaypoints($current, $neighbor), $neighbor);

                if ($tentativeGScore < $gScore[$neighbor->symbol]) {
                    $cameFrom[$neighbor->symbol] = $current;

                    $gScore[$neighbor->symbol] = $tentativeGScore;
                    $fScore[$neighbor->symbol] = $tentativeGScore + $heuristic($neighbor, $goal);

                    if (!array_find($openSet, fn (SystemWaypoint $waypoint) => $waypoint->symbol === $neighbor->symbol)) {
                        $openSet[] = $neighbor;
                    }
                }
            }
        } while (count($openSet));

        return [];
    }

    /**
     * @param array<string, SystemWaypoint> $cameFrom
     * @return array<SystemWaypoint>
     */
    private function reconstructPath(array $cameFrom, SystemWaypoint $current): array
    {
        $totalPath = [$current];

        while (array_key_exists($current->symbol, $cameFrom)) {
            $current = $cameFrom[$current->symbol];
            array_unshift($totalPath, $current);
        }

        return $totalPath;
    }

    /**
     * @param array<SystemWaypoint> $openSet
     * @param array<float> $fScore
     */
    private function lowestScoreWaypoint(array $openSet, array $fScore): SystemWaypoint
    {
        $lowestScore = PHP_FLOAT_MAX;
        $lowestWaypoint = null;

        foreach ($openSet as $waypoint) {
            if ($fScore[$waypoint->symbol] < $lowestScore) {
                $lowestScore = $fScore[$waypoint->symbol];
                $lowestWaypoint = $waypoint;
            }
        }

        return $lowestWaypoint;
    }

    private function addWeight(float $distance, SystemWaypoint $waypoint): float
    {
        if (in_array($waypoint->symbol, $this->fuelWaypoints)) {
            return $distance * 0.25;
        }

        return $distance;
    }

    /**
     * @return array<SystemWaypoint>
     */
    private function findNeighboringWaypoints(SystemWaypoint $from, float $maxDistance = 200.0): array
    {
        $connectingWaypoints = [];

        foreach ($this->system->waypoints as $waypoint) {
            $distance = $this->distanceBetweenWaypoints($from, $waypoint);
            if ($distance <= $maxDistance) {
                $connectingWaypoints[] = $waypoint;
            }
        }

        return $connectingWaypoints;
    }

    private function distanceBetweenWaypoints(SystemWaypoint $from, SystemWaypoint $to): float
    {
        return sqrt(pow($to->x - $from->x, 2) + pow($to->y - $from->y, 2));
    }
}
