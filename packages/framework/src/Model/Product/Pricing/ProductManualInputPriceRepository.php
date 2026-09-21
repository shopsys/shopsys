<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductManualInputPriceRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getProductManualInputPriceRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductManualInputPrice::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice[]
     */
    public function getByProduct(Product $product): array
    {
        return $this->getProductManualInputPriceRepository()->findBy(['product' => $product]);
    }

    public function findByProductAndPricingGroup(
        Product $product,
        PricingGroup $pricingGroup,
    ): ?ProductManualInputPrice {
        if ($product->getId() === null) {
            return null;
        }

        return $this->getProductManualInputPriceRepository()->find([
            'product' => $product->getId(),
            'pricingGroup' => $pricingGroup->getId(),
        ]);
    }

    /**
     * @param int[] $productIds
     */
    public function preloadByProductIdsAndPricingGroup(array $productIds, PricingGroup $pricingGroup): void
    {
        if ($productIds === []) {
            return;
        }

        $this->em->createQueryBuilder()
            ->select('pmip')
            ->from(ProductManualInputPrice::class, 'pmip')
            ->where('pmip.product IN (:productIds)')
            ->andWhere('pmip.pricingGroup = :pricingGroup')
            ->setParameter('productIds', $productIds)
            ->setParameter('pricingGroup', $pricingGroup)
            ->getQuery()
            ->getResult();
    }
}
