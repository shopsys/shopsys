<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'shopsys:recalculations',
    description: 'Run all recalculations',
)]
class RecalculationsCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository $categoryVisibilityRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator
     */
    public function __construct(
        private readonly CategoryVisibilityRepository $categoryVisibilityRepository,
        private readonly ProductVisibilityFacade $productVisibilityFacade,
        private readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Running recalculations:');
        $output->writeln('<fg=green>Categories visibility.</fg=green>');
        $this->categoryVisibilityRepository->refreshCategoriesVisibility();

        $output->writeln('<fg=green>Products visibility.</fg=green>');
        $this->productVisibilityFacade->calculateProductVisibilityForAll();

        $output->writeln('<fg=green>Products selling denial.</fg=green>');
        $this->productSellingDeniedRecalculator->calculateSellingDeniedForAll();

        return Command::SUCCESS;
    }
}
