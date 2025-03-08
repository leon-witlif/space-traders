<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\ContractFactory;
use App\Storage\ContractStorage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('app:contract:run')]
class ContractCommand extends Command
{
    public function __construct(
        private readonly ContractStorage $contractStorage,
        private readonly ContractFactory $contractFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('contract', mode: InputArgument::REQUIRED);

        $this->addArgument('runs', default: 10);
        $this->addArgument('wait', default: 85);

        $this->addOption('once', mode: InputOption::VALUE_NONE, description: 'Whether to run the exection once');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('once')) {
            $this->executeContractTask($input, $output);
        } else {
            $runs = $input->getArgument('runs');

            while (--$runs >= 0) {
                $this->executeContractTask($input, $output);
                sleep($input->getArgument('wait'));
            }
        }

        return Command::SUCCESS;
    }

    private function executeContractTask(InputInterface $input, OutputInterface $output): void
    {
        $data = $this->contractStorage->get($input->getArgument('contract'));

        $contract = $this->contractFactory->createProcurementContract($data['agentToken'], $data['contractId'], $data['shipSymbol']);
        $contract->restoreFromArray($data['tasks']);
        $contract->execute();

        $this->contractStorage->updateField(
            $this->contractStorage->key($data['contractId']),
            'tasks',
            $contract
        );

        $output->writeln((string) $contract);
    }
}
