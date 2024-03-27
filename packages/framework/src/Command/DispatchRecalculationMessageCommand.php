<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum $productRecalculationPriorityEnum
     */
    public function __construct(
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly ProductRecalculationPriorityEnum $productRecalculationPriorityEnum,
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
            )
            ->addOption(
                'priority',
                'p',
                InputOption::VALUE_OPTIONAL,
                sprintf('Define the message priority. Possible values are: %s', $this->productRecalculationPriorityEnum->getPipeSeparatedValues()),
                ProductRecalculationPriorityEnum::REGULAR,
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
        $priority = $input->getOption('priority');

        if ($priority === null) {
            $symfonyStyle->error(sprintf('Invalid priority value. Possible values are: %s', $this->productRecalculationPriorityEnum->getPipeSeparatedValues()));

            return Command::FAILURE;
        }

        if ($shouldRecalculateAll && count($productIds) > 0) {
            $symfonyStyle->error('You cannot use both `--all` and product IDs at the same time');

            return Command::FAILURE;
        }

        if ($shouldRecalculateAll && $priority === ProductRecalculationPriorityEnum::HIGH) {
            $symfonyStyle->error('Dispatching all products to the high priority queue is not supported');

            return Command::FAILURE;
        }

        if ($shouldRecalculateAll) {
            return $this->executeAll($symfonyStyle);
        }

        return $this->executeIds($productIds, $symfonyStyle, $priority);
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
     * @param string $priority
     * @return int
     */
    protected function executeIds(
        array $productIds,
        SymfonyStyle $symfonyStyle,
        string $priority,
    ): int {
        try {
            Assert::allNumeric($productIds, 'All product IDs must be numeric');
            Assert::notEmpty($productIds, 'You must specify at least one product ID');
        } catch (InvalidArgumentException $e) {
            $symfonyStyle->error($e->getMessage());

            return Command::FAILURE;
        }

        $dispatchedProductIds = $this->productRecalculationDispatcher->dispatchProductIds($productIds, $priority);
        $symfonyStyle->success(['Dispatched message for IDs', implode(', ', $dispatchedProductIds), sprintf('Priority: %s', $priority)]);

        return Command::SUCCESS;
    }
}
