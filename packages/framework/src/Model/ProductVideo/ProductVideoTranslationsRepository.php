<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductVideo;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ProductVideoTranslationsRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductVideoTranslations::class);
    }

    public function findById(int $id): ?ProductVideoTranslations
    {
        return $this->getRepository()->find($id);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\ProductVideo\ProductVideoTranslations[]
     */
    public function findByProductVideoId(int $id): array
    {
        return $this->getRepository()->findBy(['productVideo' => $id]);
    }

    public function findByProductVideoIdAndLocale(int $id, string $locale): ?ProductVideoTranslations
    {
        return $this->getRepository()->findOneBy(['productVideo' => $id, 'locale' => $locale]);
    }
}
