<?php

declare(strict_types=1);

namespace App\Model\Product\Series;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductSeriesProductFacade
{
    /**
     * @var \App\Model\Product\Series\ProductSeriesProductRepository
     */
    private $productSeriesProductRepository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Product\Series\ProductSeriesFacade
     */
    private $productSeriesFacade;

    /**
     * @param \App\Model\Product\Series\ProductSeriesProductRepository $productSeriesProductRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Product\Series\ProductSeriesFacade $productSeriesFacade
     */
    public function __construct(
        ProductSeriesProductRepository $productSeriesProductRepository,
        EntityManagerInterface $em,
        ProductSeriesFacade $productSeriesFacade
    ) {
        $this->productSeriesProductRepository = $productSeriesProductRepository;
        $this->em = $em;
        $this->productSeriesFacade = $productSeriesFacade;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param string[] $productSeriesCodeList
     */
    public function editProductSeriesProductRelation(Product $product, array $productSeriesCodeList): void
    {
        $productSeriesCodeList = array_combine($productSeriesCodeList, $productSeriesCodeList);

        $canFlush = false;
        $productSeriesProducts = $this->productSeriesProductRepository->findByProduct($product);
        foreach ($productSeriesProducts as $productSeriesProduct) {
            $currentAkeneoCode = $productSeriesProduct->getProductSeries()->getAkeneoCode();
            if (in_array($currentAkeneoCode, $productSeriesCodeList, true)) {
                unset($productSeriesCodeList[$currentAkeneoCode]);
            } else {
                $this->em->remove($productSeriesProduct);
                $canFlush = true;
            }
        }

        foreach ($productSeriesCodeList as $akeneoCode) {
            $productSeries = $this->productSeriesFacade->findByAkeneoCode($akeneoCode);
            if ($productSeries !== null) {
                $productSeriesProduct = new ProductSeriesProduct($productSeries, $product);
                $this->em->persist($productSeriesProduct);
                $canFlush = true;
            }
        }

        if ($canFlush) {
            $this->em->flush();
        }
    }

    /**
     * @param \App\Model\Product\Series\ProductSeries $productSeries
     * @return \App\Model\Product\Product[]
     */
    public function findAvailableProductsByProductSeries(ProductSeries $productSeries): array
    {
        return $this->productSeriesProductRepository->findAvailableProductsByProductSeries($productSeries);
    }
}
