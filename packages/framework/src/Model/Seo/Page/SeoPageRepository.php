<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Seo\Page\Exception\SeoPageNotFoundException;

class SeoPageRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function getById(int $seoPageId): SeoPage
    {
        /** @var \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage|null $seoPage */
        $seoPage = $this->getSeoPageRepository()->find($seoPageId);

        if ($seoPage === null) {
            $message = sprintf('SeoPage with ID %d not found.', $seoPageId);

            throw new SeoPageNotFoundException($message);
        }

        return $seoPage;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPage[]
     */
    public function getAll(): array
    {
        return $this->getSeoPageRepository()->findAll();
    }

    public function getAllQueryBuilder(): QueryBuilder
    {
        return $this->getSeoPageRepository()->createQueryBuilder('sp')
            ->select('sp, spd')
            ->join('sp.domains', 'spd');
    }

    protected function getSeoPageRepository(): EntityRepository
    {
        return $this->em->getRepository(SeoPage::class);
    }

    public function getByDomainIdAndPageSlug(int $domainId, string $pageSlug): SeoPage
    {
        $seoPage = $this->findByDomainIdAndPageSlug($domainId, $pageSlug);

        if ($seoPage === null) {
            $message = sprintf('SeoPage with slug \'%s\' not found.', $pageSlug);

            throw new SeoPageNotFoundException($message);
        }

        return $seoPage;
    }

    public function findByDomainIdAndPageSlug(int $domainId, string $pageSlug): ?SeoPage
    {
        $matchingSeoPageIdSubQueryDql = $this->em->createQueryBuilder()
            ->select('IDENTITY(matchingSeoPageDomain.seoPage)')
            ->from(SeoPageDomain::class, 'matchingSeoPageDomain')
            ->where('matchingSeoPageDomain.domainId = :domainId')
            ->andWhere('matchingSeoPageDomain.pageSlug = :pageSlug')
            ->getDQL();

        return $this->getSeoPageRepository()
            ->createQueryBuilder('sp')
            ->select('sp, spd')
            ->join('sp.domains', 'spd')
            ->where('sp.id IN (' . $matchingSeoPageIdSubQueryDql . ')')
            ->setParameter('domainId', $domainId)
            ->setParameter('pageSlug', $pageSlug)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
