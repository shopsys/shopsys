<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;

class ProductReviewFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductReviewRepository $productReviewRepository,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    public function getById(int $productReviewId): ProductReview
    {
        return $this->productReviewRepository->getById($productReviewId);
    }

    public function edit(ProductReview $productReview, ProductReviewData $productReviewData): void
    {
        $productReview->edit($productReviewData);

        $this->em->flush();

        $this->dispatchReviewsExport($productReview);
    }

    protected function dispatchReviewsExport(ProductReview $productReview): void
    {
        $product = $productReview->getProduct();

        if ($product === null) {
            return;
        }

        $mainProduct = $product->isVariant() ? $product->getMainVariant() : $product;

        $this->productRecalculationDispatcher->dispatchProducts(
            [$mainProduct],
            ProductRecalculationPriorityEnum::HIGH,
            [ProductExportScopeConfig::SCOPE_PRODUCT_REVIEWS],
        );
    }

    /**
     * {@see \Shopsys\FrameworkBundle\Model\ProductReview\ProductReviewRepository::getApprovedByMainProductForExport()}
     *
     * @return \Shopsys\FrameworkBundle\Model\ProductReview\ProductReview[]
     */
    public function getApprovedByMainProductForExport(Product $mainProduct, int $domainId): array
    {
        return $this->productReviewRepository->getApprovedByMainProductForExport($mainProduct, $domainId);
    }
}
