<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductCalculatedPriceRepository
{
    protected EntityManagerInterface $em;

    protected ProductCalculatedPriceFactoryInterface $productCalculatedPriceFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPriceFactoryInterface $productCalculatedPriceFactory
     */
    public function __construct(EntityManagerInterface $em, ProductCalculatedPriceFactoryInterface $productCalculatedPriceFactory)
    {
        $this->em = $em;
        $this->productCalculatedPriceFactory = $productCalculatedPriceFactory;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getProductCalculatedPriceRepository()
    {
        return $this->em->getRepository(ProductCalculatedPrice::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $priceWithVat
     */
    public function saveCalculatedPrice(Product $product, PricingGroup $pricingGroup, ?Money $priceWithVat)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice|null $productCalculatedPrice */
        $productCalculatedPrice = $this->getProductCalculatedPriceRepository()->find([
            'product' => $product->getId(),
            'pricingGroup' => $pricingGroup->getId(),
        ]);

        if ($productCalculatedPrice === null) {
            $productCalculatedPrice = $this->productCalculatedPriceFactory->create(
                $product,
                $pricingGroup,
                $priceWithVat
            );
            $this->em->persist($productCalculatedPrice);
        } else {
            $productCalculatedPrice->setPriceWithVat($priceWithVat);
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     */
    public function createProductCalculatedPricesForPricingGroup(PricingGroup $pricingGroup)
    {
        $this->em->getConnection()->executeStatement(
            'INSERT INTO product_calculated_prices (product_id, pricing_group_id, price_with_vat)
            SELECT id, :pricingGroupId, NULL FROM products',
            [
                'pricingGroupId' => $pricingGroup->getId(),
            ],
            [
                'pricingGroupId' => Types::INTEGER,
            ]
        );
    }
}
