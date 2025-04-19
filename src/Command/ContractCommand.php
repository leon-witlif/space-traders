<?php

declare(strict_types=1);

namespace App\Command;

use App\Contract\ContractFactory;
use App\Contract\IdleFarm;
use App\Contract\Procurement;
use App\Contract\Task;
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

        $this->addOption('runs', mode: InputOption::VALUE_REQUIRED, default: 2880);
        $this->addOption('wait', mode: InputOption::VALUE_REQUIRED, default: 10);

        $this->addOption('once', mode: InputOption::VALUE_NONE, description: 'Whether to run the exection once');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('once')) {
            $this->executeContractTask($input, $output);
        } else {
            $runs = (int) $input->getOption('runs');

            while (--$runs >= 0) {
                $this->executeContractTask($input, $output);

                if ($runs > 0) {
                    $output->writeln('Remaining runs: '.$runs.PHP_EOL);
                    sleep((int) $input->getOption('wait'));
                }
            }
        }

        return Command::SUCCESS;
    }

    private function executeContractTask(InputInterface $input, OutputInterface $output): void
    {
        $data = $this->contractStorage->get($input->getArgument('contract'));

        if (!$data) {
            $output->writeln('Unable to find the specified contract');

            return;
        }

        /** @var array<int, array{task: class-string<Task>, args: array<int, mixed>, finished: bool}> $tasks */
        $tasks = $data['tasks'];

        $contract = match ($data['contractId']) {
            'idle-farm' => $this->contractFactory->createContract(IdleFarm::class, $data['agentToken'], $data['shipSymbol']),
            default => $this->contractFactory->createContract(Procurement::class, $data['agentToken'], $data['contractId'], $data['shipSymbol']),
        };

        $contract->restoreFromArray($tasks);
        $contract->execute();

        $this->contractStorage->updateField(
            $this->contractStorage->key($data['contractId']),
            'tasks',
            $contract
        );

        $output->writeln((string) $contract);
    }
}
