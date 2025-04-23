<?php

declare(strict_types=1);

namespace App\SpaceTrader\Endpoint;

use App\SpaceTrader\ApiClient;
use App\SpaceTrader\ApiEndpoint;
use App\SpaceTrader\Exception\ShipRefuelException;
use App\SpaceTrader\Exception\ShipSellException;
use App\SpaceTrader\Struct\Cooldown;
use App\SpaceTrader\Struct\Ship;
use App\SpaceTrader\Struct\ShipCargo;
use App\SpaceTrader\Struct\ShipNav;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class FleetApi implements ApiEndpoint
{
    private readonly LoggerInterface $logger;

    public function __construct(private readonly ApiClient $apiClient)
    {
        $this->logger = new Logger('api');
        $this->logger->pushHandler(new RotatingFileHandler(__DIR__.'/../../../var/log/api.log', 3, Level::Debug));
    }

    /**
     * @return array<Ship>
     */
    public function list(string $token, bool $disableCache = false): array
    {
        if (!$disableCache) {
            $this->apiClient->prepareRequestCache('ship-list');
        }

        $response = $this->apiClient->makeAgentRequest('GET', '/my/ships', $token);

        return array_map(fn (array $ship) => Ship::fromResponse($ship), $response['data']);
    }

    public function get(string $token, string $shipSymbol, bool $disableCache = false): Ship
    {
        if (!$disableCache) {
            $this->apiClient->prepareRequestCache("ship-$shipSymbol");
        }

        $response = $this->apiClient->makeAgentRequest('GET', "/my/ships/$shipSymbol", $token);

        return Ship::fromResponse($response['data']);
    }

    public function orbit(string $token, string $shipSymbol): void
    {
        $this->apiClient->clearRequestCache('ship-list', "ship-$shipSymbol");

        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/orbit", $token);
    }

    public function dock(string $token, string $shipSymbol): void
    {
        $this->apiClient->clearRequestCache('ship-list', "ship-$shipSymbol");

        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/dock", $token);
    }

    /**
     * @return array{
     *     cooldown: Cooldown,
     *     extraction: array<string, mixed>,
     *     cargo: ShipCargo,
     *     events: array<int, array<string, mixed>>
     * }
     */
    public function extract(string $token, string $shipSymbol): array
    {
        $this->apiClient->clearRequestCache('ship-list', "ship-$shipSymbol");

        $response = $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/extract", $token);
        $content = $response['data'];

        $content['cargo'] = ShipCargo::fromResponse($content['cargo']);

        $this->logger->info('Extract: '.count($content['events']).' events occured');

        foreach ($content['events'] as $event) {
            $this->logger->info("Symbol: {$event['symbol']} Component: {$event['component']} Name: {$event['name']} Description: {$event['description']}");
        }

        return $content;
    }

    public function jettison(string $token, string $shipSymbol, string $symbol, int $units): ShipCargo
    {
        $this->apiClient->clearRequestCache('ship-list', "ship-$shipSymbol");

        $data = [
            'symbol' => $symbol,
            'units' => $units,
        ];

        $response = $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/jettison", $token, ['body' => json_encode($data)]);

        return ShipCargo::fromResponse($response['data']['cargo']);
    }

    /**
     * @return array{
     *     fuel: array<string, mixed>,
     *     nav: ShipNav,
     *     events: array<int, array<string, mixed>>
     * }
     */
    public function navigate(string $token, string $shipSymbol, string $waypointSymbol): array
    {
        $this->apiClient->clearRequestCache('ship-list', "ship-$shipSymbol");

        $data = [
            'waypointSymbol' => $waypointSymbol,
        ];

        $response = $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/navigate", $token, ['body' => json_encode($data)]);
        $content = $response['data'];

        $content['nav'] = ShipNav::fromResponse($content['nav']);

        $this->logger->info('Navigate: '.count($content['events']).' events occured');

        foreach ($content['events'] as $event) {
            $this->logger->info("Symbol: {$event['symbol']} Component: {$event['component']} Name: {$event['name']} Description: {$event['description']}");
        }

        return $content;
    }

    public function nav(string $token, string $shipSymbol, string $flightMode): void
    {
        $this->apiClient->clearRequestCache('ship-list', "ship-$shipSymbol");

        $data = [
            'flightMode' => $flightMode,
        ];

        $this->apiClient->makeAgentRequest('PATCH', "/my/ships/$shipSymbol/nav", $token, ['body' => json_encode($data)]);
    }

    public function sell(string $token, string $shipSymbol, string $symbol, int $units): void
    {
        $this->apiClient->clearRequestCache('ship-list', "ship-$shipSymbol");
        $this->apiClient->throwException(ShipSellException::class);

        $data = [
            'symbol' => $symbol,
            'units' => $units,
        ];

        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/sell", $token, ['body' => json_encode($data)]);
    }

    /**
     * @phpstan-throws ShipRefuelException
     */
    public function refuel(string $token, string $shipSymbol): void
    {
        $this->apiClient->clearRequestCache('ship-list', "ship-$shipSymbol");
        $this->apiClient->throwException(ShipRefuelException::class);

        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/refuel", $token);
    }

    public function negotiate(string $token, string $shipSymbol): void
    {
        $this->apiClient->clearRequestCache('ship-list', "ship-$shipSymbol");

        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/negotiate/contract", $token);
    }

    public function repair(string $token, string $shipSymbol): void
    {
        $this->apiClient->clearRequestCache('ship-list', "ship-$shipSymbol");

        $this->apiClient->makeAgentRequest('POST', "/my/ships/$shipSymbol/repair", $token);
    }
}
