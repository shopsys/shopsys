<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\EntityLog\Model\EntityLogNoteRegistry;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;

class ProductReviewFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductReviewRepository $productReviewRepository,
        protected readonly ProductReviewFactory $productReviewFactory,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
        protected readonly EntityLogNoteRegistry $entityLogNoteRegistry,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    public function getById(int $productReviewId): ProductReview
    {
        return $this->productReviewRepository->getById($productReviewId);
    }

    public function create(ProductReviewData $productReviewData): ProductReview
    {
        $productReview = $this->productReviewFactory->create($productReviewData);

        $this->em->persist($productReview);
        $this->em->flush();

        if ($productReview->getStatus() === ProductReviewStatusEnum::STATUS_APPROVED) {
            $this->dispatchReviewsExport($productReview);
        }

        return $productReview;
    }

    public function edit(ProductReview $productReview, ProductReviewData $productReviewData): void
    {
        if ($productReview->isContentEdited($productReviewData) && $productReviewData->contentChangeReason !== null) {
            $this->entityLogNoteRegistry->registerNote($productReview, $productReviewData->contentChangeReason);
        }

        $productReview->edit($productReviewData);

        $this->em->flush();

        $this->dispatchReviewsExport($productReview);
    }

    protected function dispatchReviewsExport(ProductReview $productReview): void
    {
        $productId = $productReview->getProduct()?->getId();

        if ($productId === null) {
            return;
        }

        $this->productRecalculationDispatcher->dispatchProductIds(
            [$productId],
            ProductRecalculationPriorityEnum::HIGH,
            [ProductExportScopeConfig::SCOPE_PRODUCT_REVIEWS],
        );

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(
            CleanStorefrontCacheFacade::PROMOTED_PRODUCTS_QUERY_KEY_PART,
        );
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\ProductReview\ProductReview[]
     */
    public function getApprovedByMainProductForExport(Product $mainProduct, int $domainId): array
    {
        return $this->productReviewRepository->getApprovedByMainProductForExport($mainProduct, $domainId);
    }

    /**
     * @param int[]|null $productIds Null returns the reviews regardless of the reviewed product
     * @return \Shopsys\FrameworkBundle\Model\ProductReview\ProductReview[]
     */
    public function getByCustomerUser(
        CustomerUser $customerUser,
        int $domainId,
        ?array $productIds,
        int $limit,
        int $offset,
    ): array {
        return $this->productReviewRepository->getByCustomerUser($customerUser, $domainId, $productIds, $limit, $offset);
    }

    /**
     * @param int[]|null $productIds Null counts the reviews regardless of the reviewed product
     */
    public function getCountByCustomerUser(
        CustomerUser $customerUser,
        int $domainId,
        ?array $productIds,
    ): int {
        return $this->productReviewRepository->getCountByCustomerUser($customerUser, $domainId, $productIds);
    }

    public function existsByCustomerUserAndProductId(
        CustomerUser $customerUser,
        int $productId,
        int $domainId,
    ): bool {
        return $this->productReviewRepository->existsByCustomerUserAndProductId($customerUser, $productId, $domainId);
    }

    public function existsByOrderAndProductId(Order $order, int $productId): bool
    {
        return $this->productReviewRepository->existsByOrderAndProductId($order, $productId);
    }

    /**
     * Uuids of the order's products that already have a review linked to the order, regardless of the review status
     *
     * @return string[]
     */
    public function getReviewedProductUuidsByOrder(Order $order): array
    {
        return $this->productReviewRepository->getReviewedProductUuidsByOrder($order);
    }
}
