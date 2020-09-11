<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository as FrameworkProductRepository;

class ProductRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(FrameworkProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @param string $uuid
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getSellableByUuid(string $uuid, int $domainId, PricingGroup $pricingGroup): Product
    {
        $qb = $this->productRepository->getAllSellableQueryBuilder($domainId, $pricingGroup);
        $qb->andWhere('p.uuid = :uuid');
        $qb->setParameter('uuid', $uuid);

        $product = $qb->getQuery()->getOneOrNullResult();

        if ($product === null) {
            throw new ProductNotFoundException(
                sprintf('Product with UUID "%s" does not exist.', $uuid)
            );
        }

        return $product;
    }
}
