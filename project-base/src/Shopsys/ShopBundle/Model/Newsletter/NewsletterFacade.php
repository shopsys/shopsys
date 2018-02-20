<?php

namespace Shopsys\ShopBundle\Model\Newsletter;

use DateTimeImmutable;
use Doctrine\ORM\EntityManager;

class NewsletterFacade
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Shopsys\ShopBundle\Model\Newsletter\NewsletterRepository
     */
    private $newsletterRepository;

    public function __construct(
        EntityManager $em,
        NewsletterRepository $newsletterRepository
    ) {
        $this->em = $em;
        $this->newsletterRepository = $newsletterRepository;
    }

    /**
     * @param string $email
     * @param int $domainId
     */
    public function addSubscribedEmail($email, $domainId)
    {
        if (!$this->newsletterRepository->existsSubscribedEmailByDomainId($email, $domainId)) {
            $newsletterSubscriber = new NewsletterSubscriber($email, new DateTimeImmutable(), $domainId);
            $this->em->persist($newsletterSubscriber);
            $this->em->flush($newsletterSubscriber);
        }
    }

    /**
     * @param int DomainId
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getAllEmailsDataIteratorByDomainId($domainId)
    {
        return $this->newsletterRepository->getAllEmailsDataIteratorByDomain($domainId);
    }

    /**
     * @param $email
     * @return \Shopsys\ShopBundle\Model\Newsletter\NewsletterSubscriber
     */
    public function getNewsletterSubscriberByEmailAndDomain($email, $domainId)
    {
        return $this->newsletterRepository->getNewsletterSubscribeByEmailAndDomain($email, $domainId);
    }
}
