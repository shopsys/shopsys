<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Product\Product;
use SortDirection;

class ProductAccessoryRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function getProductAccessoryRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductAccessory::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessory[]
     */
    public function getAllByProduct(Product $product): array
    {
        return $this->getProductAccessoryRepository()->findBy(['product' => $product], ['position' => SortDirection::Ascending]);
    }
}
