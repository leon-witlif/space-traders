<?php

declare(strict_types=1);

namespace App\Navigation;

use App\Helper\Navigation;
use App\SpaceTrader\Struct\System;
use App\SpaceTrader\SystemApi;
use App\Storage\WaypointStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;

class Navigator extends AbstractController
{
    public readonly System $system;

    private array $waypoints;
    private int $ttl;

    public function __construct(
        private readonly SystemApi $systemApi,
        private readonly WaypointStorage $waypointStorage,
    ) {
    }

    public function initializeSystem(string $systemSymbol): void
    {
        $this->system = $this->systemApi->get($systemSymbol);

        foreach ($this->system->waypoints as $waypoint) {
            if (!$this->waypointStorage->get($waypoint['symbol'])) {
                $this->waypointStorage->add(['waypointSymbol' => $waypoint['symbol'], 'scanned' => false]);
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
                    'type' => $waypointResponse['type'],
                    'x' => $waypointResponse['x'],
                    'y' => $waypointResponse['y'],
                    'traits' => array_map(fn (array $trait) => $trait['symbol'], $waypointResponse['traits']),
                    'factionSymbol' => $waypointResponse['faction']['symbol'],
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

    public function calculateRoute(string $fromWaypointSymbol, string $toWaypointSymbol): array
    {
        $fromWaypoint = $this->waypointStorage->get($fromWaypointSymbol);
        $toWaypoint = $this->waypointStorage->get($toWaypointSymbol);

        $this->waypoints = [$fromWaypoint];
        $this->ttl = 100;

        $this->findNextWaypoint($fromWaypoint, $toWaypoint);

        return [
            'farthestWaypoint' => $this->findFarthestWaypoint($fromWaypoint),
            'connectingWaypoints' => [
                'from' => $fromWaypoint,
                'connections' => $this->findConnectingWaypoints($fromWaypoint, 300.0),
            ],
            'route' => $this->waypoints,
        ];
    }

    private function findNextWaypoint($currentWaypoint, $targetWaypoint): void
    {
        if (--$this->ttl < 0) {
            return;
        }

        $connectingWaypoints = $this->findConnectingWaypoints($currentWaypoint, 300.0);
        $canReachTarget = false;

        foreach ($connectingWaypoints as $waypoint) {
            if ($waypoint['waypointSymbol'] === $targetWaypoint['waypointSymbol']) {
                $canReachTarget = true;
                break;
            }
        }

        // dump($currentWaypoint, $connectingWaypoints);

        $closestDistanceToTarget = 1000.0;
        $closestWaypointToTarget = [];

        foreach ($connectingWaypoints as $waypoint) {
            $distance = $this->distanceBetweenWaypoints($waypoint['x'], $waypoint['y'], $targetWaypoint['x'], $targetWaypoint['y']);
            if ($distance < $closestDistanceToTarget) {
                $closestDistanceToTarget = $distance;
                $closestWaypointToTarget = $waypoint;
            }
        }

        // dump($closestDistanceToTarget, $closestWaypointToTarget);
        // dd();

        $this->waypoints[] = $closestWaypointToTarget;

        if (!$canReachTarget) {
            $this->findNextWaypoint($closestWaypointToTarget, $targetWaypoint);
        }
    }

    private function findFarthestWaypoint(array $fromWaypoint): string
    {
        $scannedWaypoints = array_values(array_filter($this->waypointStorage->list(), fn (array $market) => $market['scanned']));

        $farthestDistance = .0;
        $farthestWaypoint = [];

        foreach ($scannedWaypoints as $waypoint) {
            $distance = $this->distanceBetweenWaypoints($fromWaypoint['x'], $fromWaypoint['y'], $waypoint['x'], $waypoint['y']);
            if ($distance > $farthestDistance) {
                $farthestDistance = $distance;
                $farthestWaypoint = $waypoint;
            }
        }

        return $farthestWaypoint['waypointSymbol'];
    }

    private function findConnectingWaypoints(array $fromWaypoint, float $maxDistance): array
    {
        $scannedWaypoints = array_values(array_filter($this->waypointStorage->list(), fn (array $market) => $market['scanned']));

        $connectingWaypoints = [];

        foreach ($scannedWaypoints as $waypoint) {
            $distance = $this->distanceBetweenWaypoints($fromWaypoint['x'], $fromWaypoint['y'], $waypoint['x'], $waypoint['y']);
            if ($distance <= $maxDistance) {
                $connectingWaypoints[] = $waypoint;
            }
        }

        return $connectingWaypoints;
    }

    private function distanceBetweenWaypoints(int $fromX, int $fromY, int $toX, int $toY): float
    {
        return sqrt(pow($toX - $fromX, 2) + pow($toY - $fromY, 2));
    }
}
