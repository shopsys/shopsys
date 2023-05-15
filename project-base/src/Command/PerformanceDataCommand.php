<?php

declare(strict_types=1);

namespace App\Command;

use App\DataFixtures\Performance\CategoryDataFixture;
use App\DataFixtures\Performance\CustomerUserDataFixture;
use App\DataFixtures\Performance\OrderDataFixture;
use App\DataFixtures\Performance\ProductDataFixture;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PerformanceDataCommand extends Command
{
    /**
     * @var string
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnlySniff.ReferenceViaFullyQualifiedName
     */
    protected static $defaultName = 'shopsys:performance-data';

    /**
     * @param \App\DataFixtures\Performance\CategoryDataFixture $categoryDataFixture
     * @param \App\DataFixtures\Performance\ProductDataFixture $productDataFixture
     * @param \App\DataFixtures\Performance\CustomerUserDataFixture $customerUserDataFixture
     * @param \App\DataFixtures\Performance\OrderDataFixture $orderDataFixture
     */
    public function __construct(
        private readonly CategoryDataFixture $categoryDataFixture,
        private readonly ProductDataFixture $productDataFixture,
        private readonly CustomerUserDataFixture $customerUserDataFixture,
        private readonly OrderDataFixture $orderDataFixture,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(
                'Import performance data to test db. Demo and base data fixtures must be imported first.',
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<fg=green>loading ' . CategoryDataFixture::class . '</fg=green>');
        $this->categoryDataFixture->load($output);
        $output->writeln('<fg=green>loading ' . ProductDataFixture::class . '</fg=green>');
        $this->productDataFixture->load($output);
        $output->writeln('<fg=green>loading ' . CustomerUserDataFixture::class . '</fg=green>');
        $this->customerUserDataFixture->load($output);
        $output->writeln('<fg=green>loading ' . OrderDataFixture::class . '</fg=green>');
        $this->orderDataFixture->load($output);

        return Command::SUCCESS;
    }
}
