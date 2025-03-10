<?php

declare(strict_types=1);

namespace App\Contract;

use App\Contract\Task\DeliverTask;
use App\Contract\Task\ExtractTask;
use App\Contract\Task\FindAsteroidTask;
use App\Contract\Task\JettisonTask;
use App\Contract\Task\NavigateToTask;
use App\Contract\Task\RefuelTask;
use App\SpaceTrader\ApiRegistry;
use App\SpaceTrader\Struct\ContractDeliverGood;
use App\SpaceTrader\Struct\ShipCargo;
use App\SpaceTrader\Struct\ShipCargoItem;

class Procurement extends Contract
{
    public function __construct(
        ApiRegistry $apiRegistry,
        public readonly string $agentToken,
        public readonly string $contractId,
        public readonly string $shipSymbol,
    ) {
        parent::__construct($apiRegistry);
    }

    protected function executeTask(Task $task): void
    {
        $task->execute($this->agentToken, $output);

        switch ($task::class) {
            case FindAsteroidTask::class:
                if ($task->finished && $output) {
                    $task->insertAfter($this->createTask(NavigateToTask::class, $this->shipSymbol, $output));
                }
                break;
            case RefuelTask::class:
                if ($task->finished) {
                    $ship = $this->getShipApi()->get($this->agentToken, $this->shipSymbol, true);

                    if ($ship->nav->waypointSymbol === 'X1-BS3-CE5B') {
                        $task->insertAfter($this->createTask(ExtractTask::class, $this->shipSymbol));
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
                            $task->overwriteState(true);

                            $agent = $this->getAgentApi()->get($this->agentToken, true);

                            $navigateToDeliverTask = $this->createTask(NavigateToTask::class, $this->shipSymbol, $contract->terms->deliver[0]->destinationSymbol);
                            $deliverTask = $this->createTask(DeliverTask::class, $contract->id, $this->shipSymbol, [$contract->terms->deliver[0]->tradeSymbol]);
                            $navigateToHeadquartersTask = $this->createTask(NavigateToTask::class, $this->shipSymbol, $agent->headquarters);

                            $task->insertAfter($navigateToDeliverTask);
                            $navigateToDeliverTask->insertAfter($deliverTask);
                            $deliverTask->insertAfter($navigateToHeadquartersTask);

                            return;
                        }

                        if ($this->isShipCargoItemExceedingThreshold($shipCargoItem, $shipCargo)) {
                            $task->overwriteState(true);

                            $ship = $this->getShipApi()->get($this->agentToken, $this->shipSymbol, true);
                            $extractWaypoint = $ship->nav->waypointSymbol;

                            $navigateToDeliverTask = $this->createTask(NavigateToTask::class, $this->shipSymbol, $contract->terms->deliver[0]->destinationSymbol);
                            $deliverTask = $this->createTask(DeliverTask::class, $contract->id, $this->shipSymbol, [$contract->terms->deliver[0]->tradeSymbol]);
                            $navigateToExtractTask = $this->createTask(NavigateToTask::class, $this->shipSymbol, $extractWaypoint);

                            $task->insertAfter($navigateToDeliverTask);
                            $navigateToDeliverTask->insertAfter($deliverTask);
                            $deliverTask->insertAfter($navigateToExtractTask);

                            return;
                        }
                    }

                    if ($this->isShipCargoFull($shipCargo)) {
                        $task->overwriteState(true);

                        $jettisonTask = $this->createTask(JettisonTask::class, $this->shipSymbol, [$contractDeliverGood->tradeSymbol]);
                        $extractTask = $this->createTask(ExtractTask::class, $this->shipSymbol);

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
