<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @internal This class is not intended to be used directly from your code. Use ProductRecalculationDispatcher
 */
class ProductRecalculationDispatcherExecutor
{
    public function __construct(
        protected readonly ProductRecalculationRepository $productRecalculationRepository,
        protected readonly ProductRecalculationDeduplicationFacade $productRecalculationDeduplicationFacade,
        protected readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * @param int[] $productIds
     * @param string[] $exportScopes
     * @return int[]
     */
    public function dispatchProductIds(
        array $productIds,
        string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): array {
        $productIdsWithMainVariants = $this->productRecalculationRepository->replaceVariantIdsWithMainVariantIds($productIds);

        $productIdsToDispatch = $this->productRecalculationDeduplicationFacade->updateScopesAndReturnProductIdsToDispatch(
            $productIdsWithMainVariants,
            $exportScopes,
            $productRecalculationPriorityEnum,
        );

        if ($productIdsToDispatch === []) {
            return [];
        }

        foreach ($productIdsToDispatch as $productId) {
            $message = match ($productRecalculationPriorityEnum) {
                ProductRecalculationPriorityEnum::HIGH => new ProductRecalculationPriorityHighMessage($productId),
                ProductRecalculationPriorityEnum::REGULAR => new ProductRecalculationPriorityRegularMessage($productId),
                default => throw new UnknownProductRecalculationPriorityException($productRecalculationPriorityEnum),
            };

            $this->messageBus->dispatch($message);
        }

        return $productIdsToDispatch;
    }
}
