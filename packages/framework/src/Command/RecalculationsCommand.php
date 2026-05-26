<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Override;
use Shopsys\FrameworkBundle\Model\Category\CategoryVisibilityRepository;
use Shopsys\FrameworkBundle\Model\Product\Flag\PromotionFlagFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftFlagSynchronizerFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: RecalculationsCommand::COMMAND_NAME,
    description: 'Run all recalculations',
)]
class RecalculationsCommand extends Command
{
    public const string COMMAND_NAME = 'shopsys:recalculations';

    public function __construct(
        protected readonly CategoryVisibilityRepository $categoryVisibilityRepository,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
        protected readonly GiftFlagSynchronizerFacade $giftFlagSynchronizerFacade,
        protected readonly PromotionFlagFacade $promotionFlagFacade,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Running recalculations:');
        $output->writeln('<fg=green>Categories visibility.</fg=green>');
        $this->categoryVisibilityRepository->refreshCategoriesVisibility();

        $output->writeln('<fg=green>Products visibility.</fg=green>');
        $this->productVisibilityFacade->calculateProductVisibilityForAll();

        $output->writeln('<fg=green>Products selling denial.</fg=green>');
        $this->productSellingDeniedRecalculator->calculateSellingDeniedForAll();

        $output->writeln('<fg=green>Gift products automatic flags.</fg=green>');
        $this->giftFlagSynchronizerFacade->refreshAllGiftPlans();

        $output->writeln('<fg=green>X + Y promotion products automatic flags.</fg=green>');
        $this->promotionFlagFacade->updatePromotionFlagsForAll();

        return Command::SUCCESS;
    }
}
