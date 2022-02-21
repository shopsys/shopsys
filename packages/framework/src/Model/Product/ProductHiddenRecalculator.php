<?php

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;

class ProductHiddenRecalculator
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator
     */
    protected $em;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->em = $entityManager;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function calculateHiddenForProduct(Product $product)
    {
        $this->executeQuery($product);
    }

    public function calculateHiddenForAll()
    {
        $this->executeQuery();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|null $product
     */
    protected function executeQuery(Product $product = null)
    {
        $qb = $this->em->createQueryBuilder()
            ->update(Product::class, 'p')
            ->set('p.calculatedHidden', '
                CASE
                    WHEN p.usingStock = TRUE
                        AND p.variantType <> :mainType
                        AND p.stockQuantity <= 0
                        AND p.outOfStockAction = :outOfStockActionHide
                    THEN TRUE
                    ELSE p.hidden
                END
                ')
            ->setParameter('outOfStockActionHide', Product::OUT_OF_STOCK_ACTION_HIDE)
            ->setParameter('mainType', Product::VARIANT_TYPE_MAIN);

        if ($product !== null) {
            $qb->where('p = :product')->setParameter('product', $product);
        }

        $qb->getQuery()->execute();
    }
}
