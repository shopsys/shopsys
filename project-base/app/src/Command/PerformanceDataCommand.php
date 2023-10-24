<?php

declare(strict_types=1);

namespace App\Command;

use App\DataFixtures\Performance\CategoryDataFixture;
use App\DataFixtures\Performance\CustomerUserDataFixture;
use App\DataFixtures\Performance\OrderDataFixture;
use App\DataFixtures\Performance\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
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
        private readonly Setting $setting,
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
        if ($this->setting->get(Setting::PERFORMANCE_DATA_PRODUCTS_IMPORTED) === 0) {
            $output->writeln('<fg=green>loading ' . CategoryDataFixture::class . '</fg=green>');
            $this->categoryDataFixture->load($output);
        }

        if ($this->setting->get(Setting::PERFORMANCE_DATA_PRODUCTS_IMPORTED) === $this->productDataFixture->getProductTotalCount()) {
            $output->writeln('<fg=green>performance data already imported</fg=green>');
        } else {
            $output->writeln('<fg=green>loading ' . ProductDataFixture::class . '</fg=green>');
            $imported = $this->productDataFixture->load($output, $this->setting->get(Setting::PERFORMANCE_DATA_PRODUCTS_IMPORTED));

            echo "Setting imported to $imported\n";

            $this->setting->set(Setting::PERFORMANCE_DATA_PRODUCTS_IMPORTED, $imported);

            return Command::SUCCESS;
        }

        $output->writeln('<fg=green>loading ' . CustomerUserDataFixture::class . '</fg=green>');
        $this->customerUserDataFixture->load($output);
        $output->writeln('<fg=green>loading ' . OrderDataFixture::class . '</fg=green>');
        $this->orderDataFixture->load($output);

        $this->setting->set(Setting::PERFORMANCE_DATA_PRODUCTS_IMPORTED, 0);

        return Command::SUCCESS;
    }
}
