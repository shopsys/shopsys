<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

#[AsCommand(name: 'shopsys:dispatch:recalculations')]
class DispatchRecalculationMessageCommand extends Command
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setDescription('Dispatch messages with product IDs to recalculate')
            ->addArgument(
                'productIds',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'Product IDs to recalculate, separated with space',
            )
            ->addOption(
                'all',
                null,
                InputOption::VALUE_NONE,
                'Dispatch messages to recalculate all products',
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $productIds = $input->getArgument('productIds');
        $shouldRecalculateAll = $input->getOption('all');

        if ($shouldRecalculateAll && count($productIds) > 0) {
            $symfonyStyle->error('You cannot use both `--all` and product IDs at the same time');

            return Command::FAILURE;
        }

        if ($shouldRecalculateAll) {
            return $this->executeAll($symfonyStyle);
        }

        return $this->executeIds($productIds, $symfonyStyle);
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @return int
     */
    protected function executeAll(SymfonyStyle $symfonyStyle): int
    {
        $this->productRecalculationDispatcher->dispatchAllProducts();
        $symfonyStyle->success('Dispatched all products to recalculate');

        return Command::SUCCESS;
    }

    /**
     * @param int[] $productIds
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @return int
     */
    protected function executeIds(array $productIds, SymfonyStyle $symfonyStyle): int
    {
        try {
            Assert::allNumeric($productIds, 'All product IDs must be numeric');
            Assert::notEmpty($productIds, 'You must specify at least one product ID');
        } catch (InvalidArgumentException $e) {
            $symfonyStyle->error($e->getMessage());

            return Command::FAILURE;
        }

        $dispatchedProductIds = $this->productRecalculationDispatcher->dispatchProductIds($productIds);
        $symfonyStyle->success(['Dispatched message for IDs', implode(', ', $dispatchedProductIds)]);

        return Command::SUCCESS;
    }
}
