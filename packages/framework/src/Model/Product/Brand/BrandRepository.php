<?php

namespace Shopsys\FrameworkBundle\Model\Product\Brand;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Brand\Exception\BrandNotFoundException;

class BrandRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getBrandRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(Brand::class);
    }

    /**
     * @param int $brandId
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function getById(int $brandId): \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
    {
        $brand = $this->getBrandRepository()->find($brandId);

        if ($brand === null) {
            $message = 'Brand with ID ' . $brandId . ' not found.';
            throw new BrandNotFoundException($message);
        }

        return $brand;
    }

    /**
     * @return object[]
     */
    public function getAll(): array
    {
        return $this->getBrandRepository()->findBy([], ['name' => 'asc']);
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand
     */
    public function getOneByUuid(string $uuid): Brand
    {
        $brand = $this->getBrandRepository()->findOneBy(['uuid' => $uuid]);

        if ($brand === null) {
            throw new BrandNotFoundException('Brand with UUID ' . $uuid . ' does not exist.');
        }

        return $brand;
    }

    /**
     * @param string[] $uuids
     * @return object[]
     */
    public function getByUuids(array $uuids): array
    {
        return $this->getBrandRepository()->findBy(['uuid' => $uuids]);
    }
}
