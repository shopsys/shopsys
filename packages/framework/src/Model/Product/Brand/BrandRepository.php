<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\ORM\EntityManagerInterface;

class BrandRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    protected function getBrandRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Brand::class);
    }

    public function getById(int $brandId): \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
    {
        $brand = $this->getBrandRepository()->find($brandId);

        if ($brand === null) {
            $message = 'Brand with ID ' . $brandId . ' not found.';
            throw new \Shopsys\FrameworkBundle\Model\Product\Brand\Exception\BrandNotFoundException($message);
        }

        return $brand;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getAll(): array
    {
        return $this->getBrandRepository()->findBy([], ['name' => 'asc']);
    }
}
