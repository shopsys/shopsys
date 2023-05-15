<?php

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Doctrine\ORM\EntityManagerInterface;

class TopProductFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductRepository $topProductRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProductFactoryInterface $topProductFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly TopProductRepository $topProductRepository,
        protected readonly TopProductFactoryInterface $topProductFactory,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProduct[]
     */
    public function getAll($domainId)
    {
        return $this->topProductRepository->getAll($domainId);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllOfferedProducts($domainId, $pricingGroup)
    {
        return $this->topProductRepository->getOfferedProductsForTopProductsOnDomain($domainId, $pricingGroup);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function saveTopProductsForDomain($domainId, array $products)
    {
        $oldTopProducts = $this->topProductRepository->getAll($domainId);

        foreach ($oldTopProducts as $oldTopProduct) {
            $this->em->remove($oldTopProduct);
        }
        $this->em->flush();

        $position = 1;

        foreach ($products as $product) {
            $topProduct = $this->topProductFactory->create($product, $domainId, $position++);
            $this->em->persist($topProduct);
        }
        $this->em->flush();
    }
}
