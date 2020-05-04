<?php

declare(strict_types=1);

namespace App\Model\Newsletter;

use DateTime;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterRepository as BaseNewsletterRepository;

/**
 * @method \App\Model\Newsletter\NewsletterSubscriber getNewsletterSubscriberById(int $id)
 * @method \App\Model\Newsletter\NewsletterSubscriber|null findNewsletterSubscribeByEmailAndDomainId(string $email, int $domainId)
 */
class NewsletterRepository extends BaseNewsletterRepository
{
    /**
     * @inheritdoc
     */
    public function getAllEmailsDataIteratorByDomainId($domainId): IterableResult
    {
        $query = $this->getNewsletterSubscriberRepository()
            ->createQueryBuilder('ns')
            ->select('ns.email, ns.createdAt')
            ->where('ns.domainId = :domainId')
            ->andWhere('ns.deleted = false')
            ->setParameter('domainId', $domainId)
            ->getQuery();

        return $query->iterate(null, AbstractQuery::HYDRATE_SCALAR);
    }

    /**
     * @inheritdoc
     */
    public function getQueryBuilderForQuickSearch(int $domainId, QuickSearchFormData $searchData): QueryBuilder
    {
        $queryBuilder = $this->getNewsletterSubscriberRepository()
            ->createQueryBuilder('ns')
            ->select('ns.id, ns.email, ns.createdAt')
            ->where('ns.domainId = :domainId')
            ->andWhere('ns.deleted = false')
            ->setParameter('domainId', $domainId);

        if ($searchData->text !== null && $searchData->text !== '') {
            $queryBuilder->andWhere('NORMALIZE(ns.email) LIKE NORMALIZE(:searchData)')
                ->setParameter('searchData', DatabaseSearching::getFullTextLikeSearchString($searchData->text));
        }

        return $queryBuilder;
    }

    /**
     * @param int $domainId
     * @param \DateTime $lastSyncDateTime
     * @return \App\Model\Newsletter\NewsletterSubscriber[]
     */
    public function getAllUpdatedSubscribersFromLastUpdate(int $domainId, DateTime $lastSyncDateTime): array
    {
        $query = $this->getNewsletterSubscriberRepository()
            ->createQueryBuilder('ns')
            ->where('ns.domainId = :domainId')
            ->andWhere('ns.updatedAt > :lastSyncDateTime')
            ->setParameter('domainId', $domainId)
            ->setParameter('lastSyncDateTime', $lastSyncDateTime->format(\DateTimeInterface::ATOM))
            ->getQuery();

        return $query->getResult();
    }
}
