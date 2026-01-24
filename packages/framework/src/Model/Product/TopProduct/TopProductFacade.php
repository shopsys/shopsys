<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

class TopProductFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly TopProductRepository $topProductRepository,
        protected readonly TopProductFactory $topProductFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProduct[]
     */
    public function getAll(int $domainId): array
    {
        return $this->topProductRepository->getAll($domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getOfferedProducts(int $domainId, PricingGroup $pricingGroup, ?int $limit): array
    {
        return $this->topProductRepository->getOfferedProductsForTopProductsOnDomain($domainId, $pricingGroup, $limit);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     */
    public function saveTopProductsForDomain(int $domainId, array $products): void
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
