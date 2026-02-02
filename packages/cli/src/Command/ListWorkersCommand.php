<?php

declare(strict_types=1);

namespace Shopsys\Cli\Command;

use Exception;
use Override;
use Shopsys\Cli\Worker\WorkerInterface;
use Shopsys\Cli\Worker\WorkerRunner;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'workers',
    description: 'List all available configuration workers',
)]
final class ListWorkersCommand extends Command
{
    /**
     * @param \Shopsys\Cli\Worker\WorkerRunner $workerRunner
     */
    public function __construct(
        private readonly WorkerRunner $workerRunner,
    ) {
        parent::__construct();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Available Configuration Workers');

        try {
            $workers = $this->workerRunner->getWorkers();
        } catch (Exception $e) {
            $io->error('Failed to load workers: ' . $e->getMessage());

            return Command::FAILURE;
        }

        $io->section('Backend Workers');
        $io->table(
            ['Worker', 'Description', 'Priority'],
            $this->getWorkersInfo($workers, 'Backend'),
        );

        $io->section('Storefront Workers');
        $io->table(
            ['Worker', 'Description', 'Priority'],
            $this->getWorkersInfo($workers, 'Storefront'),
        );

        return Command::SUCCESS;
    }

    /**
     * @param array<\Shopsys\Cli\Worker\WorkerInterface> $workers
     * @param string $type
     * @return array<int, array<int, string>>
     */
    private function getWorkersInfo(array $workers, string $type): array
    {
        $filteredWorkers = array_filter(
            $workers,
            static fn (WorkerInterface $worker) => str_contains(get_class($worker), '\\' . $type . '\\'),
        );

        return array_map(
            static function (WorkerInterface $worker) {
                return [
                    $worker->getName(),
                    $worker->getDescription(),
                    (string)$worker->getPriority(),
                ];
            },
            $filteredWorkers,
        );
    }
}
