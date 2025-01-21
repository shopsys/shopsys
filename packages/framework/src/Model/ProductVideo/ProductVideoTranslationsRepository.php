<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ProductVideoTranslationsRepository
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
        return $this->em->getRepository(ProductVideoTranslations::class);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoTranslations|null
     */
    public function findById(int $id): ?ProductVideoTranslations
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoTranslations[]
     */
    public function findByProductVideoId(int $id): array
    {
        return $this->getRepository()->findBy(['productVideo' => $id]);
    }

    /**
     * @param int $id
     * @param string $locale
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoTranslations|null
     */
    public function findByProductVideoIdAndLocale(int $id, string $locale): ?ProductVideoTranslations
    {
        return $this->getRepository()->findOneBy(['productVideo' => $id, 'locale' => $locale]);
    }
}
