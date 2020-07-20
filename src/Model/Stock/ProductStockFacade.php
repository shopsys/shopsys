<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductStockFacade
{
    /**
     * @var \App\Model\Stock\ProductStockRepository
     */
    private $productStockRepository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \App\Model\Stock\ProductStockRepository $stockProductRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        ProductStockRepository $stockProductRepository,
        EntityManagerInterface $em
    ) {
        $this->productStockRepository = $stockProductRepository;
        $this->em = $em;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProduct(Product $product): array
    {
        return $this->productStockRepository->getProductStocksByProduct($product);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProductIndexedByStockId(Product $product): array
    {
        $productStocks = $this->getProductStocksByProduct($product);
        $productStocksById = [];
        foreach ($productStocks as $productStock) {
            $productStocksById[$productStock->getStock()->getId()] = $productStock;
        }

        return $productStocksById;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProductAndDomainIdIndexedByStockId(Product $product, int $domainId): array
    {
        $productStocksById = [];
        foreach ($this->getProductStocksByProductIndexedByStockId($product) as $id => $productStock) {
            if ($productStock->getStock()->getDomainId() === $domainId) {
                $productStocksById[$id] = $productStock;
            }
        }

        return $productStocksById;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Stock\Stock $stock
     * @param \App\Model\Stock\ProductStockData $productStockData
     */
    public function editProductStockRelation(Product $product, Stock $stock, ProductStockData $productStockData): void
    {
        $productStock = $this->productStockRepository->findProductStockByStockAndProduct($stock, $product);
        if (!$productStock) {
            $productStock = new ProductStock($stock, $product);
            $this->em->persist($productStock);
        }
        $productStock->edit($productStockData);

        $this->em->flush();
    }

    /**
     * @param string $productCatnum
     * @param string $stockExternalId
     * @return \App\Model\Stock\ProductStock|null
     */
    public function findProductStockByProductCatnumAndStockExternalId(string $productCatnum, string $stockExternalId): ?ProductStock
    {
        return $this->productStockRepository->findProductStockByStockExternalIdAndProductCatnum($stockExternalId, $productCatnum);
    }

    public function resetFutureProductStockAfterNowDate(): void
    {
        $futureProductStockAfterNowDate = $this->productStockRepository->findFutureProductStockAfterNowDate();
        foreach ($futureProductStockAfterNowDate as $productStock) {
            $productStock->setDateOfStorage(null);
            $productStock->setFutureProductQuantity(null);
        }
        $this->em->flush();
    }
}
