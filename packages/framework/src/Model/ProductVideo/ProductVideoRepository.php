<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ProductVideoRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductVideo::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo|null
     */
    public function findById(int $id): ?ProductVideo
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideo[]
     */
    public function findByProductId(int $id): array
    {
        return $this->getRepository()->findBy([
            'product' => $id,
        ]);
    }
}
