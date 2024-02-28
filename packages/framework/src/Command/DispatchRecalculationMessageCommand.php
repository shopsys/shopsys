<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Command;

use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnumInterface;
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
            )
            ->addOption(
                'priority',
                'p',
                InputOption::VALUE_OPTIONAL,
                sprintf('Define the message priority. Possible values are: %s', ProductRecalculationPriorityEnum::getPipeSeparatedValues()),
                ProductRecalculationPriorityEnum::REGULAR->value,
            )
            ->addOption(
                'scope',
                's',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Define the message scopes. Run "shopsys:list:export-scopes" command for listing available scopes',
                [],
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $productIds = $input->getArgument('productIds');
        $scopes = $input->getOption('scope');
        $shouldRecalculateAll = $input->getOption('all');
        $priority = ProductRecalculationPriorityEnum::tryFrom($input->getOption('priority'));

        if ($priority === null) {
            $symfonyStyle->error(sprintf('Invalid priority value. Possible values are: %s', ProductRecalculationPriorityEnum::getPipeSeparatedValues()));

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
            return $this->executeAll($symfonyStyle, $scopes);
        }

        return $this->executeIds($productIds, $symfonyStyle, $priority, $scopes);
    }

    /**
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @param string[] $scopes
     * @return int
     */
    protected function executeAll(SymfonyStyle $symfonyStyle, array $scopes): int
    {
        $this->productRecalculationDispatcher->dispatchAllProducts($scopes);
        $message = 'Dispatched all products to recalculate';

        if (count($scopes) > 0) {
            $message .= sprintf(' with scopes: %s', implode(', ', $scopes));
        }
        $symfonyStyle->success($message);

        return Command::SUCCESS;
    }

    /**
     * @param int[] $productIds
     * @param \Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum $priority
     * @param string[] $scopes
     * @return int
     */
    protected function executeIds(
        array $productIds,
        SymfonyStyle $symfonyStyle,
        ProductRecalculationPriorityEnumInterface $priority,
        array $scopes,
    ): int {
        try {
            Assert::allNumeric($productIds, 'All product IDs must be numeric');
            Assert::notEmpty($productIds, 'You must specify at least one product ID');
        } catch (InvalidArgumentException $e) {
            $symfonyStyle->error($e->getMessage());

            return Command::FAILURE;
        }

        $dispatchedProductIds = $this->productRecalculationDispatcher->dispatchProductIds($productIds, $priority, $scopes);
        $symfonyStyle->success([
            'Dispatched message for IDs', implode(', ', $dispatchedProductIds),
            sprintf('Priority: %s', $priority->value),
            sprintf('Scopes: %s', count($scopes) > 0 ? implode(', ', $scopes) : '-'),
        ]);

        return Command::SUCCESS;
    }
}
