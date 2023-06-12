<?php

declare(strict_types=1);

namespace App\Model\SeoPage;

use App\Model\SeoPage\Exception\SeoPageNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class SeoPageRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param int $seoPageId
     * @return \App\Model\SeoPage\SeoPage
     */
    public function getById(int $seoPageId): SeoPage
    {
        /** @var \App\Model\SeoPage\SeoPage|null $seoPage */
        $seoPage = $this->getSeoPageRepository()->find($seoPageId);

        if ($seoPage === null) {
            $message = sprintf('SeoPage with ID %d not found.', $seoPageId);

            throw new SeoPageNotFoundException($message);
        }

        return $seoPage;
    }

    /**
     * @return \App\Model\SeoPage\SeoPage[]
     */
    public function getAll(): array
    {
        return $this->getSeoPageRepository()->findAll();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getSeoPageRepository()->createQueryBuilder('sp');
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getSeoPageRepository(): EntityRepository
    {
        return $this->em->getRepository(SeoPage::class);
    }
}
