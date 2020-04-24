<?php

declare(strict_types=1);


namespace App\Model\Product\Package;


use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ProductPackageRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductPackage::class);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('pp')
            ->from(ProductPackage::class, 'pp')
            ;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Product\Package\ProductPackage[]
     */
    public function getProductPackagesByProduct(Product $product): array
    {
        return $this->getRepository()->findBy(['product'=>$product]);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderForProductPackagesByProduct(Product $product): QueryBuilder
    {
        return $this->getQueryBuilder()
            ->where('pp.product = :product')
            ->setParameter('product', $product)
            ;
    }
}