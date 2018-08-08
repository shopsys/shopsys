<?php

namespace Shopsys\FrameworkBundle\Model\Newsletter;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class NewsletterRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    protected function getNewsletterSubscriberRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(NewsletterSubscriber::class);
    }
    
    public function existsSubscribedEmail(string $email, int $domainId): bool
    {
        return $this->getNewsletterSubscriberRepository()->findOneBy(['email' => $email, 'domainId' => $domainId]) !== null;
    }
    
    public function getAllEmailsDataIteratorByDomainId(int $domainId): \Doctrine\ORM\Internal\Hydration\IterableResult
    {
        $query = $this->getNewsletterSubscriberRepository()
            ->createQueryBuilder('ns')
            ->select('ns.email, ns.createdAt')
            ->where('ns.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery();

        return $query->iterate(null, AbstractQuery::HYDRATE_SCALAR);
    }

    public function getQueryBuilderForQuickSearch(int $domainId, QuickSearchFormData $searchData): \Doctrine\ORM\QueryBuilder
    {
        $queryBuilder = $this->getNewsletterSubscriberRepository()
            ->createQueryBuilder('ns')
            ->select('ns.id, ns.email, ns.createdAt')
            ->where('ns.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        if ($searchData->text !== null && $searchData->text !== '') {
            $queryBuilder->andWhere('NORMALIZE(ns.email) LIKE NORMALIZE(:searchData)')
                ->setParameter('searchData', DatabaseSearching::getFullTextLikeSearchString($searchData->text));
        }

        return $queryBuilder;
    }

    public function getNewsletterSubscriberById(int $id): \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber
    {
        return $this->getNewsletterSubscriberRepository()->find($id);
    }
    
    public function findNewsletterSubscribeByEmailAndDomainId(string $email, int $domainId): ?\Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber
    {
        return $this->getNewsletterSubscriberRepository()
            ->findOneBy(['email' => $email, 'domainId' => $domainId]);
    }
}
