<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class OrderProductFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductHiddenRecalculator $productHiddenRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductSellingDeniedRecalculator $productSellingDeniedRecalculator
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Model\Module\ModuleFacade $moduleFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductHiddenRecalculator $productHiddenRecalculator,
        protected readonly ProductSellingDeniedRecalculator $productSellingDeniedRecalculator,
        protected readonly ProductAvailabilityRecalculationScheduler $productAvailabilityRecalculationScheduler,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly ModuleFacade $moduleFacade,
        protected readonly ProductRepository $productRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $orderProducts
     */
    public function subtractOrderProductsFromStock(array $orderProducts): void
    {
        if ($this->moduleFacade->isEnabled(ModuleList::PRODUCT_STOCK_CALCULATIONS)) {
            $orderProductsUsingStock = $this->getOrderProductsUsingStockFromOrderProducts($orderProducts);

            foreach ($orderProductsUsingStock as $orderProductUsingStock) {
                $product = $orderProductUsingStock->getProduct();
                $product->subtractStockQuantity($orderProductUsingStock->getQuantity());
            }
            $this->em->flush();
            $this->runRecalculationsAfterStockQuantityChange($orderProducts);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $orderProducts
     */
    public function addOrderProductsToStock(array $orderProducts): void
    {
        if ($this->moduleFacade->isEnabled(ModuleList::PRODUCT_STOCK_CALCULATIONS)) {
            $orderProductsUsingStock = $this->getOrderProductsUsingStockFromOrderProducts($orderProducts);

            foreach ($orderProductsUsingStock as $orderProductUsingStock) {
                $product = $orderProductUsingStock->getProduct();
                $product->addStockQuantity($orderProductUsingStock->getQuantity());
            }
            $this->em->flush();
            $this->runRecalculationsAfterStockQuantityChange($orderProducts);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $orderProducts
     */
    protected function runRecalculationsAfterStockQuantityChange(array $orderProducts): void
    {
        $orderProductsUsingStock = $this->getOrderProductsUsingStockFromOrderProducts($orderProducts);
        $relevantProducts = [];

        foreach ($orderProductsUsingStock as $orderProductUsingStock) {
            $relevantProducts[] = $orderProductUsingStock->getProduct();
        }

        foreach ($relevantProducts as $relevantProduct) {
            $this->productSellingDeniedRecalculator->calculateSellingDeniedForProduct($relevantProduct);
            $this->productHiddenRecalculator->calculateHiddenForProduct($relevantProduct);
            $this->productAvailabilityRecalculationScheduler->scheduleProductForImmediateRecalculation(
                $relevantProduct,
            );
            $relevantProduct->markForVisibilityRecalculation();
        }
        $this->em->flush();

        $this->productVisibilityFacade->refreshProductsVisibilityForMarked();
        $this->productRepository->markProductsForExport($relevantProducts);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $orderProducts
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    protected function getOrderProductsUsingStockFromOrderProducts(array $orderProducts): array
    {
        $orderProductsUsingStock = [];

        foreach ($orderProducts as $orderProduct) {
            $product = $orderProduct->getProduct();

            if ($product !== null && $product->isUsingStock()) {
                $orderProductsUsingStock[] = $orderProduct;
            }
        }

        return $orderProductsUsingStock;
    }
}
