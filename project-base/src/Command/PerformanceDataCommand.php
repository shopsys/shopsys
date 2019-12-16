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
     */
    protected static $defaultName = 'shopsys:performance-data';

    /**
     * @var \App\DataFixtures\Performance\CategoryDataFixture
     */
    private $categoryDataFixture;

    /**
     * @var \App\DataFixtures\Performance\ProductDataFixture
     */
    private $productDataFixture;

    /**
     * @var \App\DataFixtures\Performance\CustomerUserDataFixture
     */
    private $customerUserDataFixture;

    /**
     * @var \App\DataFixtures\Performance\OrderDataFixture
     */
    private $orderDataFixture;

    /**
     * @param \App\DataFixtures\Performance\CategoryDataFixture $categoryDataFixture
     * @param \App\DataFixtures\Performance\ProductDataFixture $productDataFixture
     * @param \App\DataFixtures\Performance\CustomerUserDataFixture $customerUserDataFixture
     * @param \App\DataFixtures\Performance\OrderDataFixture $orderDataFixture
     */
    public function __construct(
        CategoryDataFixture $categoryDataFixture,
        ProductDataFixture $productDataFixture,
        CustomerUserDataFixture $customerUserDataFixture,
        OrderDataFixture $orderDataFixture
    ) {
        $this->categoryDataFixture = $categoryDataFixture;
        $this->productDataFixture = $productDataFixture;
        $this->customerUserDataFixture = $customerUserDataFixture;
        $this->orderDataFixture = $orderDataFixture;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Import performance data to test db. Demo and base data fixtures must be imported first.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<fg=green>loading ' . CategoryDataFixture::class . '</fg=green>');
        $this->categoryDataFixture->load($output);
        $output->writeln('<fg=green>loading ' . ProductDataFixture::class . '</fg=green>');
        $this->productDataFixture->load($output);
        $output->writeln('<fg=green>loading ' . CustomerUserDataFixture::class . '</fg=green>');
        $this->customerUserDataFixture->load($output);
        $output->writeln('<fg=green>loading ' . OrderDataFixture::class . '</fg=green>');
        $this->orderDataFixture->load($output);
    }
}
