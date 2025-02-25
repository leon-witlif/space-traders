<?php

declare(strict_types=1);

namespace App\Contract;

use App\Helper\Navigation;
use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\ShipApi;
use App\SpaceTrader\Struct\SystemWaypoint;
use App\SpaceTrader\SystemApi;
use Symfony\Component\HttpClient\Exception\ClientException;

class Procurement
{
    public function __construct(
        private readonly AgentApi $agentApi,
        private readonly ContractApi $contractApi,
        private readonly ShipApi $shipApi,
        private readonly SystemApi $systemApi,
        /** @var array{action: string, asteroidSymbol?: string, arrival?: string} */
        private array $data,
    ) {
    }

    /**
     * @return array{action: string, asteroidSymbol?: string, arrival?: string}
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function run(string $agentToken, string $contractId, string $shipSymbol): void
    {
        switch (ProcurementAction::{$this->data['action']}) {
            case ProcurementAction::FIND_ASTEROID:
                $agent = $this->agentApi->get($agentToken);
                $system = $this->systemApi->get(Navigation::getSystem($agent->headquarters));

                $asteroid = array_find($system->waypoints, fn (SystemWaypoint $waypoint) => $waypoint->type === 'ENGINEERED_ASTEROID');

                $this->data['action'] = ProcurementAction::NAVIGATE_TO_ASTEROID->name;
                $this->data['asteroidSymbol'] = $asteroid['symbol'];

                return;
            case ProcurementAction::NAVIGATE_TO_ASTEROID:
                $this->shipApi->orbit($agentToken, $shipSymbol);
                $navigateResponse = $this->shipApi->navigate($agentToken, $shipSymbol, $this->data['asteroidSymbol']);

                $this->data['action'] = ProcurementAction::REFUEL_SHIP->name;
                $this->data['arrival'] = $navigateResponse['nav']->route->arrival;

                return;
            case ProcurementAction::REFUEL_SHIP:
                $ship = $this->shipApi->get($agentToken, $shipSymbol);

                if ($ship->nav->status !== 'IN_ORBIT') {
                    return;
                }

                $this->shipApi->dock($agentToken, $shipSymbol);
                $this->shipApi->refuel($agentToken, $shipSymbol);
                $this->shipApi->orbit($agentToken, $shipSymbol);

                $this->data['action'] = ProcurementAction::EXTRACT_ASTEROID->name;

                return;
            case ProcurementAction::EXTRACT_ASTEROID:
                $ship = $this->shipApi->get($agentToken, $shipSymbol);

                if ($ship->cooldown->remainingSeconds > 0) {
                    return;
                }

                $contract = $this->contractApi->get($agentToken, $contractId);

                $extractResponse = $this->shipApi->extract($agentToken, $shipSymbol);

                if ($extractResponse['cargo']->units >= $extractResponse['cargo']->capacity) {
                    $this->data['action'] = ProcurementAction::JETTISON_CARGO->name;
                    // $this->data['action'] = ProcurementAction::SELL_CARGO->name;
                }

                $contractItem = $contract->terms['deliver'][0];
                $cargoItem = array_find($extractResponse['cargo']->inventory, fn (array $item) => $item['symbol'] === $contractItem['tradeSymbol']);

                if ($cargoItem && ($cargoItem['units'] >= floor($extractResponse['cargo']->capacity * 0.75) || $cargoItem['units'] >= ($contractItem['unitsRequired'] - $contractItem['unitsFulfilled']))) {
                    $this->data['action'] = ProcurementAction::NAVIGATE_TO_DELIVERY->name;
                }

                return;
            case ProcurementAction::JETTISON_CARGO:
                $contract = $this->contractApi->get($agentToken, $contractId);
                $ship = $this->shipApi->get($agentToken, $shipSymbol);

                $contractItem = $contract->terms['deliver'][0];

                $newShipCargo = null;

                foreach ($ship->cargo->inventory as $item) {
                    if ($item['symbol'] !== $contractItem['tradeSymbol']) {
                        $newShipCargo = $this->shipApi->jettison($agentToken, $shipSymbol, $item['symbol'], $item['units']);
                    }
                }

                if ($newShipCargo) {
                    $cargoItem = array_find($newShipCargo->inventory, fn (array $item) => $item['symbol'] === $contractItem['tradeSymbol']);

                    if ($cargoItem['units'] < ($contractItem['unitsRequired'] - $contractItem['unitsFulfilled'])) {
                        $this->data['action'] = ProcurementAction::EXTRACT_ASTEROID->name;
                    }

                    return;
                }

                throw new \RuntimeException('Tried to jettison no cargo');
            case ProcurementAction::SELL_CARGO:
                $contract = $this->contractApi->get($agentToken, $contractId);
                $ship = $this->shipApi->get($agentToken, $shipSymbol);

                $contractItem = $contract->terms['deliver'][0];

                $this->shipApi->dock($agentToken, $shipSymbol);

                foreach ($ship->cargo->inventory as $item) {
                    if ($item['symbol'] !== $contractItem['tradeSymbol']) {
                        try {
                            $this->shipApi->sell($agentToken, $shipSymbol, $item['symbol'], $item['units']);
                        } catch (ClientException) {
                            $this->shipApi->jettison($agentToken, $shipSymbol, $item['symbol'], $item['units']);
                        }
                    }
                }

                $this->shipApi->orbit($agentToken, $shipSymbol);

                $ship = $this->shipApi->get($agentToken, $shipSymbol);

                $cargoItem = array_find($ship->cargo->inventory, fn (array $item) => $item['symbol'] === $contractItem['tradeSymbol']);

                if ($cargoItem['units'] < ($contractItem['unitsRequired'] - $contractItem['unitsFulfilled'])) {
                    $this->data['action'] = ProcurementAction::EXTRACT_ASTEROID->name;
                }

                return;
            case ProcurementAction::NAVIGATE_TO_DELIVERY:
                $contract = $this->contractApi->get($agentToken, $contractId);

                $navigateResponse = $this->shipApi->navigate($agentToken, $shipSymbol, $contract->terms['deliver'][0]['destinationSymbol']);

                $this->data['action'] = ProcurementAction::DELIVER_CARGO->name;
                $this->data['arrival'] = $navigateResponse['nav']->route->arrival;

                return;
            case ProcurementAction::DELIVER_CARGO:
                $ship = $this->shipApi->get($agentToken, $shipSymbol);

                if ($ship->nav->status !== 'IN_ORBIT') {
                    return;
                }

                $contract = $this->contractApi->get($agentToken, $contractId);

                $this->shipApi->dock($agentToken, $shipSymbol);

                $contractItem = $contract->terms['deliver'][0];
                $cargoItem = array_find($ship->cargo->inventory, fn (array $item) => $item['symbol'] === $contractItem['tradeSymbol']);

                $this->contractApi->deliver($agentToken, $contract->id, $shipSymbol, $cargoItem['symbol'], $cargoItem['units']);

                $contract = $this->contractApi->get($agentToken, $contractId);
                $contractItem = $contract->terms['deliver'][0];

                if ($contractItem['unitsFulfilled'] < $contractItem['unitsRequired']) {
                    $this->data['action'] = ProcurementAction::NAVIGATE_TO_ASTEROID->name;
                } else {
                    $this->data['action'] = ProcurementAction::NAVIGATE_TO_HEADQUARTERS->name;
                }

                $this->shipApi->orbit($agentToken, $shipSymbol);

                return;
            case ProcurementAction::NAVIGATE_TO_HEADQUARTERS:
                $agent = $this->agentApi->get($agentToken);

                $navigateResponse = $this->shipApi->navigate($agentToken, $shipSymbol, $agent->headquarters);

                $this->data['action'] = ProcurementAction::FINISH_CONTRACT->name;
                $this->data['arrival'] = $navigateResponse['nav']->route->arrival;

                return;
            case ProcurementAction::FINISH_CONTRACT:
                $ship = $this->shipApi->get($agentToken, $shipSymbol);

                if ($ship->nav->status !== 'IN_ORBIT') {
                    return;
                }

                $this->shipApi->dock($agentToken, $shipSymbol);
                $this->shipApi->refuel($agentToken, $shipSymbol);

                $this->contractApi->fulfill($agentToken, $contractId);

                return;
        }
    }
}
