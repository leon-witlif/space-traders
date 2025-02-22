<?php

declare(strict_types=1);

namespace App\Contract;

use App\Helper\Navigation;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\ShipApi;
use App\SpaceTrader\Struct\Agent;
use App\SpaceTrader\Struct\Contract;
use App\SpaceTrader\Struct\Ship;
use App\SpaceTrader\Struct\System;

class Procurement
{
    public function __construct(
        private readonly ContractApi $contractApi,
        private readonly ShipApi $shipApi,
        /** @var array{action: string, asteroid?: string} */
        private array $data,
    ) {
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function run(string $token, Agent $agent, Contract $contract, Ship $ship, System $system): void
    {
        switch (ProcurementAction::{$this->data['action']}) {
            case ProcurementAction::FIND_ASTEROID:
                $asteroid = array_find($system->waypoints, fn (array $waypoint) => $waypoint['type'] === 'ENGINEERED_ASTEROID');

                $this->data['action'] = ProcurementAction::NAVIGATE_TO_ASTEROID->name;
                $this->data['asteroid'] = $asteroid['symbol'];

                return;
            case ProcurementAction::NAVIGATE_TO_ASTEROID:
                $this->shipApi->orbitShip($token, $ship->symbol);
                $this->shipApi->navigateShip($token, $ship->symbol, $this->data['asteroid']);

                $ship = $this->shipApi->loadShip($token, $ship->symbol);

                $this->data['action'] = ProcurementAction::REFUEL_SHIP->name;
                $this->data['arrival'] = $ship->nav['route']['arrival'];

                return;
            case ProcurementAction::REFUEL_SHIP:
                if ($ship->nav['status'] !== 'IN_ORBIT') {
                    return;
                }

                $this->shipApi->dockShip($token, $ship->symbol);
                $this->shipApi->refuelShip($token, $ship->symbol);
                $this->shipApi->orbitShip($token, $ship->symbol);

                $this->data['action'] = ProcurementAction::EXTRACT_ASTEROID->name;

                return;
            case ProcurementAction::EXTRACT_ASTEROID:
                if ($ship->cooldown['remainingSeconds'] > 0) {
                    return;
                }

                $this->shipApi->extract($token, $ship->symbol);

                $ship = $this->shipApi->loadShip($token, $ship->symbol);

                if ($ship->cargo['units'] >= $ship->cargo['capacity']) {
                    $this->data['action'] = ProcurementAction::JETTISON_CARGO->name;
                }

                $contractItem = $contract->terms['deliver'][0];
                $cargoItem = array_find($ship->cargo['inventory'], fn (array $item) => $item['symbol'] === $contractItem['tradeSymbol']);

                if ($cargoItem && ($cargoItem['units'] >= floor($ship->cargo['capacity'] * 0.75) || $cargoItem['units'] >= ($contractItem['unitsRequired'] - $contractItem['unitsFulfilled']))) {
                    $this->data['action'] = ProcurementAction::NAVIGATE_TO_DELIVERY_WAYPOINT->name;
                }

                return;
            case ProcurementAction::JETTISON_CARGO:
                $contractItem = $contract->terms['deliver'][0];

                foreach ($ship->cargo['inventory'] as $item) {
                    if ($item['symbol'] !== $contractItem['tradeSymbol']) {
                        $this->shipApi->jettison($token, $ship->symbol, $item['symbol'], $item['units']);
                    }
                }

                $ship = $this->shipApi->loadShip($token, $ship->symbol);

                $cargoItem = array_find($ship->cargo['inventory'], fn (array $item) => $item['symbol'] === $contractItem['tradeSymbol']);

                if ($cargoItem['units'] < ($contractItem['unitsRequired'] - $contractItem['unitsFulfilled'])) {
                    $this->data['action'] = ProcurementAction::EXTRACT_ASTEROID->name;
                }

                return;
            case ProcurementAction::NAVIGATE_TO_DELIVERY_WAYPOINT:
                $this->shipApi->navigateShip($token, $ship->symbol, $contract->terms['deliver'][0]['destinationSymbol']);

                $ship = $this->shipApi->loadShip($token, $ship->symbol);

                $this->data['action'] = ProcurementAction::DELIVER_CARGO->name;
                $this->data['arrival'] = $ship->nav['route']['arrival'];

                return;
            case ProcurementAction::DELIVER_CARGO:
                if ($ship->nav['status'] !== 'IN_ORBIT') {
                    return;
                }

                $this->shipApi->dockShip($token, $ship->symbol);

                $contractItem = $contract->terms['deliver'][0];
                $cargoItem = array_find($ship->cargo['inventory'], fn (array $item) => $item['symbol'] === $contractItem['tradeSymbol']);

                $this->contractApi->deliverCargo($token, $contract->id, $ship->symbol, $cargoItem['symbol'], $cargoItem['units']);

                $contract = $this->contractApi->loadContract($token, $contract->id);
                $contractItem = $contract->terms['deliver'][0];

                if ($contractItem['unitsFulfilled'] < $contractItem['unitsRequired']) {
                    $this->data['action'] = ProcurementAction::NAVIGATE_TO_ASTEROID->name;
                } else {
                    $this->data['action'] = ProcurementAction::NAVIGATE_TO_HEADQUARTERS->name;
                }

                $this->shipApi->orbitShip($token, $ship->symbol);

                return;
            case ProcurementAction::NAVIGATE_TO_HEADQUARTERS:
                $this->shipApi->navigateShip($token, $ship->symbol, $agent->headquarters);

                $ship = $this->shipApi->loadShip($token, $ship->symbol);

                $this->data['action'] = ProcurementAction::FINISH_CONTRACT->name;
                $this->data['arrival'] = $ship->nav['route']['arrival'];

                return;
            case ProcurementAction::FINISH_CONTRACT:
                if ($ship->nav['status'] !== 'IN_ORBIT') {
                    return;
                }

                $this->shipApi->dockShip($token, $ship->symbol);
                $this->shipApi->refuelShip($token, $ship->symbol);

                $this->contractApi->fulfillContract($token, $contract->id);

                return;
        }
    }
}
