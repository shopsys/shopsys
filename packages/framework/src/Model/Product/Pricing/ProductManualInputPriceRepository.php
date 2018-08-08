<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductManualInputPriceRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getProductManualInputPriceRepository()
    {
        return $this->em->getRepository(ProductManualInputPrice::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice[]
     */
    public function getByProduct(Product $product)
    {
        return $this->getProductManualInputPriceRepository()->findBy(['product' => $product]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig[] $domainConfigs
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice[]
     */
    public function getByProductAndDomainConfigs(Product $product, array $domainConfigs)
    {
        if (count($domainConfigs) === 0) {
            return [];
        }

        $domainIds = [];
        foreach ($domainConfigs as $domainConfig) {
            $domainIds[] = $domainConfig->getId();
        }

        $queryBuilder = $this->getProductManualInputPriceRepository()->createQueryBuilder('pmp')
            ->join('pmp.pricingGroup', 'pg')
            ->andWhere('pmp.product = :product')->setParameter('product', $product)
            ->andWhere('pg.domainId IN (:domainsIds)')->setParameter('domainsIds', $domainIds);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice|null
     */
    public function findByProductAndPricingGroup(Product $product, PricingGroup $pricingGroup)
    {
        return $this->getProductManualInputPriceRepository()->findOneBy([
            'product' => $product,
            'pricingGroup' => $pricingGroup,
        ]);
    }
}
