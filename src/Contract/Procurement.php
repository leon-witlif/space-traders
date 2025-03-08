<?php

declare(strict_types=1);

namespace App\Contract;

use App\Contract\Task\DeliverTask;
use App\Contract\Task\ExtractTask;
use App\Contract\Task\JettisonTask;
use App\Contract\Task\NavigateToTask;
use App\Helper\Navigation;
use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\ShipApi;
use App\SpaceTrader\Struct\ShipCargo;
use App\SpaceTrader\Struct\ShipCargoItem;
use App\SpaceTrader\Struct\SystemWaypoint;
use App\SpaceTrader\SystemApi;
use Symfony\Component\HttpClient\Exception\ClientException;

class Procurement extends Contract
{
    public function __construct(
        AgentApi $agentApi,
        ContractApi $contractApi,
        ShipApi $shipApi,
        SystemApi $systemApi,
        public readonly string $agentToken,
        public readonly string $contractId,
        public readonly string $shipSymbol,
    ) {
        parent::__construct($agentApi, $contractApi, $shipApi, $systemApi);
    }

    protected function executeTask(Task $task): void
    {
        $task->execute($this->agentToken, $output);

        if ($task::class === ExtractTask::class && $output) {
            $shipCargo = $output['cargo'];

            $contract = $this->getApi(ContractApi::class)->get($this->agentToken, $this->contractId);
            $contractItem = $contract->terms['deliver'][0];

            if ($this->isShipCargoFull($shipCargo)) {
                $task->overwriteState(true);

                $jettisonTask = $this->createTask(JettisonTask::class, $this->shipSymbol, [$contractItem['tradeSymbol']]);
                $extractTask = $this->createTask(ExtractTask::class, $this->shipSymbol);

                $task->insertAfter($jettisonTask);
                $jettisonTask->insertAfter($extractTask);

                return;
            }

            /** @var ShipCargoItem|null $cargoItem */
            $cargoItem = array_find($shipCargo->inventory, fn (ShipCargoItem $item) => $item->symbol === $contractItem['tradeSymbol']);

            if ($cargoItem) {
                if ($this->isCargoItemExceedingThreshold($cargoItem, $shipCargo)) {
                    $task->overwriteState(true);

                    $navigateToTask = $this->createTask(NavigateToTask::class, $this->shipSymbol, $contract->terms['deliver'][0]['destinationSymbol']);
                    // $deliverTask = $this->createTask(DeliverTask::class, $contract->terms['deliver'][0]['tradeSymbol']);
                }

                if ($this->wouldCargoFulfillContract($cargoItem, $contractItem)) {
                    $task->overwriteState(true);

                    $navigateToTask = $this->createTask(NavigateToTask::class, $this->shipSymbol, $contract->terms['deliver'][0]['destinationSymbol']);
                }
            }
        }
    }

    private function isShipCargoFull(ShipCargo $cargo): bool
    {
        return $cargo->units >= $cargo->capacity;
    }

    private function isCargoItemExceedingThreshold(ShipCargoItem $cargoItem, ShipCargo $cargo, float $threshold = 0.75): bool
    {
        return $cargoItem->units >= floor($cargo->capacity * $threshold);
    }

    private function wouldCargoFulfillContract(ShipCargoItem $cargoItem, array $contractItem): bool
    {
        return $cargoItem->units >= ($contractItem['unitsRequired'] - $contractItem['unitsFulfilled']);
    }

    public function run(string $agentToken, string $contractId, string $shipSymbol): void
    {
        switch (ProcurementAction::{$this->data['action']}) {
            case ProcurementAction::FIND_ASTEROID:
                // $agent = $this->agentApi->get($agentToken);
                // $system = $this->systemApi->get(Navigation::getSystem($agent->headquarters));
                //
                // $asteroid = array_find($system->waypoints, fn (SystemWaypoint $waypoint) => $waypoint->type === 'ENGINEERED_ASTEROID');
                //
                // $this->data['action'] = ProcurementAction::NAVIGATE_TO_ASTEROID->name;
                // $this->data['asteroidSymbol'] = $asteroid->symbol;
                //
                // return;
            case ProcurementAction::NAVIGATE_TO_ASTEROID:
                // $this->shipApi->orbit($agentToken, $shipSymbol);
                // $navigateResponse = $this->shipApi->navigate($agentToken, $shipSymbol, $this->data['asteroidSymbol']);
                //
                // $this->data['action'] = ProcurementAction::REFUEL_SHIP->name;
                // $this->data['arrival'] = $navigateResponse['nav']->route->arrival;
                //
                // return;
            case ProcurementAction::REFUEL_SHIP:
                // $ship = $this->shipApi->get($agentToken, $shipSymbol, true);
                //
                // if ($ship->nav->status !== 'IN_ORBIT') {
                //     return;
                // }
                //
                // unset($this->data['arrival']);
                //
                // $this->shipApi->dock($agentToken, $shipSymbol);
                // $this->shipApi->refuel($agentToken, $shipSymbol);
                // $this->shipApi->orbit($agentToken, $shipSymbol);
                //
                // $this->data['action'] = ProcurementAction::EXTRACT_ASTEROID->name;
                //
                // return;
            case ProcurementAction::EXTRACT_ASTEROID:
                // $ship = $this->shipApi->get($agentToken, $shipSymbol, true);
                //
                // if ($ship->cooldown->remainingSeconds > 0) {
                //     return;
                // }
                //
                // $contract = $this->contractApi->get($agentToken, $contractId, true);
                //
                // $extractResponse = $this->shipApi->extract($agentToken, $shipSymbol);
                //
                // if ($extractResponse['cargo']->units >= $extractResponse['cargo']->capacity) {
                //     $this->data['action'] = ProcurementAction::JETTISON_CARGO->name;
                //     // $this->data['action'] = ProcurementAction::SELL_CARGO->name;
                // }
                //
                // $contractItem = $contract->terms['deliver'][0];
                // $cargoItem = array_find($extractResponse['cargo']->inventory, fn (array $item) => $item['symbol'] === $contractItem['tradeSymbol']);
                //
                // if ($cargoItem && ($cargoItem['units'] >= floor($extractResponse['cargo']->capacity * 0.75) || $cargoItem['units'] >= ($contractItem['unitsRequired'] - $contractItem['unitsFulfilled']))) {
                //     $this->data['action'] = ProcurementAction::NAVIGATE_TO_DELIVERY->name;
                // }
                //
                // return;
            case ProcurementAction::JETTISON_CARGO:
                // $contract = $this->contractApi->get($agentToken, $contractId, true);
                // $ship = $this->shipApi->get($agentToken, $shipSymbol, true);
                //
                // $contractItem = $contract->terms['deliver'][0];
                //
                // $newShipCargo = null;
                //
                // foreach ($ship->cargo->inventory as $item) {
                //     if ($item['symbol'] !== $contractItem['tradeSymbol']) {
                //         $newShipCargo = $this->shipApi->jettison($agentToken, $shipSymbol, $item['symbol'], $item['units']);
                //     }
                // }
                //
                // if ($newShipCargo) {
                //     $cargoItem = array_find($newShipCargo->inventory, fn (array $item) => $item['symbol'] === $contractItem['tradeSymbol']);
                //
                //     if ($cargoItem['units'] < ($contractItem['unitsRequired'] - $contractItem['unitsFulfilled'])) {
                //         $this->data['action'] = ProcurementAction::EXTRACT_ASTEROID->name;
                //     }
                //
                //     return;
                // }
                //
                // throw new \RuntimeException('Tried to jettison no cargo');
            case ProcurementAction::SELL_CARGO:
                // $contract = $this->contractApi->get($agentToken, $contractId, true);
                // $ship = $this->shipApi->get($agentToken, $shipSymbol, true);
                //
                // $contractItem = $contract->terms['deliver'][0];
                //
                // $this->shipApi->dock($agentToken, $shipSymbol);
                //
                // foreach ($ship->cargo->inventory as $item) {
                //     if ($item['symbol'] !== $contractItem['tradeSymbol']) {
                //         try {
                //             $this->shipApi->sell($agentToken, $shipSymbol, $item['symbol'], $item['units']);
                //         } catch (ClientException) {
                //             $this->shipApi->jettison($agentToken, $shipSymbol, $item['symbol'], $item['units']);
                //         }
                //     }
                // }
                //
                // $this->shipApi->orbit($agentToken, $shipSymbol);
                //
                // $ship = $this->shipApi->get($agentToken, $shipSymbol, true);
                //
                // $cargoItem = array_find($ship->cargo->inventory, fn (array $item) => $item['symbol'] === $contractItem['tradeSymbol']);
                //
                // if ($cargoItem['units'] < ($contractItem['unitsRequired'] - $contractItem['unitsFulfilled'])) {
                //     $this->data['action'] = ProcurementAction::EXTRACT_ASTEROID->name;
                // }
                //
                // return;
            case ProcurementAction::NAVIGATE_TO_DELIVERY:
                // $contract = $this->contractApi->get($agentToken, $contractId, true);
                //
                // $navigateResponse = $this->shipApi->navigate($agentToken, $shipSymbol, $contract->terms['deliver'][0]['destinationSymbol']);
                //
                // $this->data['action'] = ProcurementAction::DELIVER_CARGO->name;
                // $this->data['arrival'] = $navigateResponse['nav']->route->arrival;
                //
                // return;
            case ProcurementAction::DELIVER_CARGO:
                $ship = $this->shipApi->get($agentToken, $shipSymbol, true);

                if ($ship->nav->status !== 'IN_ORBIT') {
                    return;
                }

                $contract = $this->contractApi->get($agentToken, $contractId, true);

                $this->shipApi->dock($agentToken, $shipSymbol);

                $contractItem = $contract->terms['deliver'][0];
                $cargoItem = array_find($ship->cargo->inventory, fn (array $item) => $item['symbol'] === $contractItem['tradeSymbol']);

                $this->contractApi->deliver($agentToken, $contract->id, $shipSymbol, $cargoItem['symbol'], $cargoItem['units']);

                $contract = $this->contractApi->get($agentToken, $contractId, true);
                $contractItem = $contract->terms['deliver'][0];

                if ($contractItem['unitsFulfilled'] < $contractItem['unitsRequired']) {
                    $this->data['action'] = ProcurementAction::NAVIGATE_TO_ASTEROID->name;
                } else {
                    $this->data['action'] = ProcurementAction::NAVIGATE_TO_HEADQUARTERS->name;
                }

                $this->shipApi->orbit($agentToken, $shipSymbol);

                return;
            case ProcurementAction::NAVIGATE_TO_HEADQUARTERS:
                $agent = $this->agentApi->get($agentToken, true);

                $navigateResponse = $this->shipApi->navigate($agentToken, $shipSymbol, $agent->headquarters);

                $this->data['action'] = ProcurementAction::FINISH_CONTRACT->name;
                $this->data['arrival'] = $navigateResponse['nav']->route->arrival;

                return;
            case ProcurementAction::FINISH_CONTRACT:
                $ship = $this->shipApi->get($agentToken, $shipSymbol, true);

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
