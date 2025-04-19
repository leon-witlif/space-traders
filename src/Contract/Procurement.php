<?php

declare(strict_types=1);

namespace App\Contract;

use App\Contract\Task\DeliverTask;
use App\Contract\Task\ExtractTask;
use App\Contract\Task\FindAsteroidTask;
use App\Contract\Task\JettisonTask;
use App\Contract\Task\NavigateToTask;
use App\Contract\Task\RefuelTask;
use App\SpaceTrader\Struct\ContractDeliverGood;
use App\SpaceTrader\Struct\ShipCargo;
use App\SpaceTrader\Struct\ShipCargoItem;

class Procurement extends Contract
{
    public function __construct(
        public readonly string $agentToken,
        public readonly string $contractId,
        public readonly string $shipSymbol,
    ) {
    }

    protected function executeTask(Task $task): void
    {
        $task->execute($this->agentToken, $output);

        switch ($task::class) {
            case FindAsteroidTask::class:
                if ($task->finished && $output) {
                    $task->insertAfter($this->initializeTask(new NavigateToTask($this->shipSymbol, $output)));
                }
                break;
            case RefuelTask::class:
                if ($task->finished) {
                    $ship = $this->getShipApi()->get($this->agentToken, $this->shipSymbol, true);

                    if ($ship->nav->waypointSymbol === 'X1-FQ80-ZD5D') {
                        $task->insertAfter($this->initializeTask(new ExtractTask($this->shipSymbol)));
                    }
                }
                break;
            case ExtractTask::class:
                if ($output) {
                    $contract = $this->getContractApi()->get($this->agentToken, $this->contractId, true);
                    $contractDeliverGood = $contract->terms->deliver[0];

                    $shipCargo = $output['cargo'];
                    /** @var ShipCargoItem|null $shipCargoItem */
                    $shipCargoItem = array_find($shipCargo->inventory, fn (ShipCargoItem $item) => $item->symbol === $contractDeliverGood->tradeSymbol);

                    if ($shipCargoItem) {
                        if ($this->wouldShipCargoFulfillContract($shipCargoItem, $contractDeliverGood)) {
                            $task->finished = true;

                            $agent = $this->getAgentApi()->get($this->agentToken, true);

                            $navigateToDeliverTask = $this->initializeTask(new NavigateToTask($this->shipSymbol, $contract->terms->deliver[0]->destinationSymbol));
                            $deliverTask = $this->initializeTask(new DeliverTask($contract->id, $this->shipSymbol, [$contract->terms->deliver[0]->tradeSymbol]));
                            $navigateToHeadquartersTask = $this->initializeTask(new NavigateToTask($this->shipSymbol, $agent->headquarters));

                            $task->insertAfter($navigateToDeliverTask);
                            $navigateToDeliverTask->insertAfter($deliverTask);
                            $deliverTask->insertAfter($navigateToHeadquartersTask);

                            return;
                        }

                        if ($this->isShipCargoItemExceedingThreshold($shipCargoItem, $shipCargo)) {
                            $task->finished = true;

                            $ship = $this->getShipApi()->get($this->agentToken, $this->shipSymbol, true);
                            $extractWaypoint = $ship->nav->waypointSymbol;

                            $navigateToDeliverTask = $this->initializeTask(new NavigateToTask($this->shipSymbol, $contract->terms->deliver[0]->destinationSymbol));
                            $deliverTask = $this->initializeTask(new DeliverTask($contract->id, $this->shipSymbol, [$contract->terms->deliver[0]->tradeSymbol]));
                            $navigateToExtractTask = $this->initializeTask(new NavigateToTask($this->shipSymbol, $extractWaypoint));

                            $task->insertAfter($navigateToDeliverTask);
                            $navigateToDeliverTask->insertAfter($deliverTask);
                            $deliverTask->insertAfter($navigateToExtractTask);

                            return;
                        }
                    }

                    if ($this->isShipCargoFull($shipCargo)) {
                        $task->finished = true;

                        $jettisonTask = $this->initializeTask(new JettisonTask($this->shipSymbol, [$contractDeliverGood->tradeSymbol]));
                        $extractTask = $this->initializeTask(new ExtractTask($this->shipSymbol));

                        $task->insertAfter($jettisonTask);
                        $jettisonTask->insertAfter($extractTask);
                    }
                }
                break;
            case NavigateToTask::class:
                if ($task->finished) {
                    $agent = $this->getAgentApi()->get($this->agentToken, true);
                    $ship = $this->getShipApi()->get($this->agentToken, $this->shipSymbol, true);

                    if ($ship->nav->waypointSymbol === $agent->headquarters) {
                        $this->getContractApi()->fulfill($this->agentToken, $this->contractId);
                    }
                }
                break;
        }
    }

    private function isShipCargoFull(ShipCargo $shipCargo): bool
    {
        return $shipCargo->units >= $shipCargo->capacity;
    }

    private function isShipCargoItemExceedingThreshold(ShipCargoItem $shipCargoItem, ShipCargo $shipCargo, float $threshold = 0.75): bool
    {
        return $shipCargoItem->units >= floor($shipCargo->capacity * $threshold);
    }

    private function wouldShipCargoFulfillContract(ShipCargoItem $shipCargoItem, ContractDeliverGood $contractDeliverGood): bool
    {
        return $shipCargoItem->units >= ($contractDeliverGood->unitsRequired - $contractDeliverGood->unitsFulfilled);
    }
}
