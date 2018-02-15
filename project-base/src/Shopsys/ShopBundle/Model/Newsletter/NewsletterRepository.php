<?php

namespace Shopsys\ShopBundle\Model\Newsletter;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityManager;
use Shopsys\ShopBundle\Component\String\DatabaseSearching;
use Shopsys\ShopBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class NewsletterRepository
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getNewsletterSubscriberRepository()
    {
        return $this->em->getRepository(NewsletterSubscriber::class);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return bool
     */
    public function existsSubscribedEmail($email, $domainId)
    {
        return $this->getNewsletterSubscriberRepository()->findOneBy(['email' => $email, 'domainId' => $domainId]) !== null;
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
     * @param \Shopsys\ShopBundle\Form\Admin\QuickSearch\QuickSearchFormData $searchData
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
            $queryBuilder->andWhere('NORMALIZE(ns.email) LIKE NORMALIZE(:searchData)')
                ->setParameter('searchData', DatabaseSearching::getFullTextLikeSearchString($searchData->text));
        }

        return $queryBuilder;
    }

    /**
     * @param int $id
     * @return \Shopsys\ShopBundle\Model\Newsletter\NewsletterSubscriber
     */
    public function getNewsletterSubscriberById(int $id)
    {
        return $this->getNewsletterSubscriberRepository()->find($id);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\ShopBundle\Model\Newsletter\NewsletterSubscriber|null
     */
    public function findNewsletterSubscribeByEmailAndDomain($email, $domainId)
    {
        return $this->getNewsletterSubscriberRepository()
            ->findOneBy(['email' => $email, 'domainId' => $domainId]);
    }
}
