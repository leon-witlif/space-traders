<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\Procurement;
use App\SpaceTrader\AgentApi;
use App\SpaceTrader\ContractApi;
use App\SpaceTrader\ShipApi;
use App\SpaceTrader\SystemApi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:contract:run')]
class ContractCommand extends Command
{
    public function __construct(
        private readonly AgentApi $agentApi,
        private readonly ContractApi $contractApi,
        private readonly ShipApi $shipApi,
        private readonly SystemApi $systemApi,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('agent', default: 'abc');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $procurement = new Procurement($this->agentApi, $this->contractApi, $this->shipApi, $this->systemApi);

        $json = <<<JSON
[
    {
        "task": "App\\\\Contract\\\\Task\\\\FindAsteroidTask",
        "args": ["ENGINEERED_ASTEROID"],
        "finished": true
    },
    {
        "task": "App\\\\Contract\\\\Task\\\\OrbitShipTask",
        "args": [],
        "finished": true
    },
    {
        "task": "App\\\\Contract\\\\Task\\\\NavigateToTask",
        "args": ["waypointSymbol"],
        "finished": true
    },
    {
        "task": "App\\\\Contract\\\\Task\\\\DockShipTask",
        "args": [],
        "finished": false
    },
    {
        "task": "App\\\\Contract\\\\Task\\\\RefuelShipTask",
        "args": [],
        "finished": false
    }
]
JSON;

        $procurement->restoreState($json);
        $procurement->execute($input->getArgument('agent'));
        $procurement->saveState();

        $output->writeln((string) $procurement);

        return Command::SUCCESS;
    }
}
