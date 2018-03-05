<?php

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use Doctrine\ORM\EntityManager;
use Shopsys\FrameworkBundle\Model\Category\Category;

class ManualBestsellingProductFacade
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductRepository
     */
    private $manualBestsellingProductRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\CachedBestsellingProductFacade
     */
    private $cachedBestsellingProductFacade;

    public function __construct(
        EntityManager $em,
        ManualBestsellingProductRepository $manualBestsellingProductRepository,
        CachedBestsellingProductFacade $cachedBestsellingProductFacade
    ) {
        $this->em = $em;
        $this->manualBestsellingProductRepository = $manualBestsellingProductRepository;
        $this->cachedBestsellingProductFacade = $cachedBestsellingProductFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $productsIndexedByPosition
     */
    public function edit(Category $category, $domainId, array $productsIndexedByPosition)
    {
        $toDelete = $this->manualBestsellingProductRepository->getByCategory($domainId, $category);
        foreach ($toDelete as $item) {
            $this->em->remove($item);
        }
        $this->em->flush();

        foreach ($productsIndexedByPosition as $position => $product) {
            if ($product !== null) {
                $manualBestsellingProduct = new ManualBestsellingProduct($domainId, $category, $product, $position);
                $this->em->persist($manualBestsellingProduct);
            }
        }
        $this->em->flush();
        $this->cachedBestsellingProductFacade->invalidateCacheByDomainIdAndCategory($domainId, $category);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsIndexedByPosition($category, $domainId)
    {
        $bestsellingProducts = $this->manualBestsellingProductRepository->getByCategory($domainId, $category);

        $products = [];
        foreach ($bestsellingProducts as $key => $bestsellingProduct) {
            $products[$key] = $bestsellingProduct->getProduct();
        }

        return $products;
    }

    /**
     * @param int $domainId
     * @return int[]
     */
    public function getCountsIndexedByCategoryId($domainId)
    {
        return $this->manualBestsellingProductRepository->getCountsIndexedByCategoryId($domainId);
    }
}
