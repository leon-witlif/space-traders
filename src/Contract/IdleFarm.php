<?php

declare(strict_types=1);

namespace App\Contract;

use App\Contract\Task\ExtractTask;
use App\Contract\Task\JettisonTask;
use App\Contract\Task\NavigateToTask;
use App\Contract\Task\RefuelTask;
use App\Contract\Task\SellTask;
use App\SpaceTrader\Struct\ShipCargoItem;

class IdleFarm extends Contract
{
    private const string MARKETPLACE = 'X1-BS3-B7';
    private const string ASTEROID = 'X1-BS3-B42';

    public function __construct(
        public readonly string $agentToken,
        public readonly string $shipSymbol,
    ) {
    }

    protected function executeTask(Task $task): void
    {
        $task->execute($this->agentToken, $output);

        switch ($task::class) {
            case RefuelTask::class:
                if ($task->finished) {
                    $ship = $this->getShipApi()->get($this->agentToken, $this->shipSymbol, true);

                    if ($ship->nav->waypointSymbol === self::MARKETPLACE) {
                        $sellCargoItems = array_map(
                            fn (ShipCargoItem $shipCargoItem) => $shipCargoItem->symbol,
                            $ship->cargo->inventory
                        );

                        $task->insertAfter($this->initializeTask(new SellTask($this->shipSymbol, $sellCargoItems)));
                    }
                }
                break;
            case SellTask::class:
                if ($task->finished) {
                    $ship = $this->getShipApi()->get($this->agentToken, $this->shipSymbol, true);

                    if ($ship->cargo->units > 0) {
                        $task->insertAfter($this->initializeTask(new JettisonTask($this->shipSymbol)));
                    } else {
                        $navigateToExtractTask = $this->initializeTask(new NavigateToTask($this->shipSymbol, self::ASTEROID));
                        $extractTask = $this->initializeTask(new ExtractTask($this->shipSymbol));

                        $task->insertAfter($navigateToExtractTask);
                        $navigateToExtractTask->insertAfter($extractTask);
                    }
                }
                break;
            case JettisonTask::class:
                if ($task->finished) {
                    $navigateToExtractTask = $this->initializeTask(new NavigateToTask($this->shipSymbol, self::ASTEROID));
                    $extractTask = $this->initializeTask(new ExtractTask($this->shipSymbol));

                    $task->insertAfter($navigateToExtractTask);
                    $navigateToExtractTask->insertAfter($extractTask);
                }
                break;
            case ExtractTask::class:
                if ($task->finished) {
                    $task->insertAfter($this->initializeTask(new NavigateToTask($this->shipSymbol, self::MARKETPLACE)));
                }
                break;
        }
    }
}
