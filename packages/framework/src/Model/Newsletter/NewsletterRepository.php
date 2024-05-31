<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Newsletter;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class NewsletterRepository
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
    protected function getNewsletterSubscriberRepository()
    {
        return $this->em->getRepository(NewsletterSubscriber::class);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return bool
     */
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
     * @param int $domainId
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getAllEmailsDataIteratorByDomainId($domainId)
    {
        $query = $this->getNewsletterSubscriberRepository()
            ->createQueryBuilder('ns')
            ->select('ns.email, ns.createdAt')
            ->where('ns.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery();

        return $query->iterate(null, AbstractQuery::HYDRATE_SCALAR);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $searchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderForQuickSearch(int $domainId, QuickSearchFormData $searchData)
    {
        $queryBuilder = $this->getNewsletterSubscriberRepository()
            ->createQueryBuilder('ns')
            ->select('ns.id, ns.email, ns.createdAt')
            ->where('ns.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        if ($searchData->text !== null && $searchData->text !== '') {
            $queryBuilder->andWhere('NORMALIZED(ns.email) LIKE NORMALIZED(:searchData)')
                ->setParameter('searchData', DatabaseSearching::getFullTextLikeSearchString($searchData->text));
        }

        return $queryBuilder;
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber|null
     */
    public function findNewsletterSubscriberById(int $id): ?NewsletterSubscriber
    {
        return $this->getNewsletterSubscriberRepository()->find($id);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber|null
     */
    public function findNewsletterSubscribeByEmailAndDomainId($email, $domainId)
    {
        return $this->getNewsletterSubscriberRepository()
            ->findOneBy([
                'email' => $email,
                'domainId' => $domainId,
            ]);
    }
}
