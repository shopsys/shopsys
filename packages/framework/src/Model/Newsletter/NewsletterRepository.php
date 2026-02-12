<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Newsletter;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class NewsletterRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    protected function getNewsletterSubscriberRepository(): EntityRepository
    {
        return $this->em->getRepository(NewsletterSubscriber::class);
    }

    public function existsSubscribedEmail(string $email, int $domainId): bool
    {
        $count = $this->getNewsletterSubscriberRepository()
            ->count([
                'email' => $email,
                'domainId' => $domainId,
            ]);

        return $count > 0;
    }

    /**
     * @return iterable<array{email: string, createdAt: \DateTimeInterface}>
     */
    public function getAllEmailsDataIteratorByDomainId(int $domainId): iterable
    {
        return $this->getNewsletterSubscriberRepository()
            ->createQueryBuilder('ns')
            ->select('ns.email, ns.createdAt')
            ->where('ns.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->toIterable([], AbstractQuery::HYDRATE_SCALAR);
    }

    public function getQueryBuilderForQuickSearch(int $domainId, QuickSearchFormData $searchData): QueryBuilder
    {
        $queryBuilder = $this->getNewsletterSubscriberRepository()
            ->createQueryBuilder('ns')
            ->select('ns.id, ns.email, ns.createdAt')
            ->where('ns.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        if ($searchData->text !== null && $searchData->text !== '') {
            $queryBuilder->andWhere('NORMALIZED(ns.email) LIKE NORMALIZED(:searchData)')
                ->setParameter('searchData', $this->databaseSearchingHelper->getFullTextLikeSearchString($searchData->text));
        }

        return $queryBuilder;
    }

    public function findNewsletterSubscriberById(int $id): ?NewsletterSubscriber
    {
        return $this->getNewsletterSubscriberRepository()->find($id);
    }

    public function findNewsletterSubscribeByEmailAndDomainId(string $email, int $domainId): ?NewsletterSubscriber
    {
        return $this->getNewsletterSubscriberRepository()
            ->findOneBy([
                'email' => $email,
                'domainId' => $domainId,
            ]);
    }
}
