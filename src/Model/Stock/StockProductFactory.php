<?php

declare(strict_types=1);


namespace App\Model\Stock;


use Doctrine\ORM\EntityManagerInterface;

class StockProductFactory
{

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\StockProduct
     */
    public function create(Stock $stock): StockProduct
    {
        $stockProduct =  new StockProduct($stock);
        $this->em->persist($stockProduct);
        $this->em->flush();
        return $stockProduct;
    }

}