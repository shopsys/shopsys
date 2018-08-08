<?php

namespace Shopsys\FrameworkBundle\Model\Newsletter;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class NewsletterFacade
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterRepository
     */
    protected $newsletterRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriberFactoryInterface
     */
    protected $newsletterSubscriberFactory;

    public function __construct(
        EntityManagerInterface $em,
        NewsletterRepository $newsletterRepository,
        NewsletterSubscriberFactoryInterface $newsletterSubscriberFactory
    ) {
        $this->em = $em;
        $this->newsletterRepository = $newsletterRepository;
        $this->newsletterSubscriberFactory = $newsletterSubscriberFactory;
    }

    /**
     * @param string $email
     * @param int $domainId
     */
    public function addSubscribedEmail($email, $domainId)
    {
        if (!$this->newsletterRepository->existsSubscribedEmail($email, $domainId)) {
            $newsletterSubscriber = $this->newsletterSubscriberFactory->create($email, new DateTimeImmutable(), $domainId);
            $this->em->persist($newsletterSubscriber);
            $this->em->flush($newsletterSubscriber);
        }
    }

    /**
     * @param int $domainId
     */
    public function getAllEmailsDataIteratorByDomainId($domainId): \Doctrine\ORM\Internal\Hydration\IterableResult
    {
        return $this->newsletterRepository->getAllEmailsDataIteratorByDomainId($domainId);
    }

    /**
     * @param string $email
     * @param int $domainId
     */
    public function findNewsletterSubscriberByEmailAndDomainId($email, $domainId): ?\Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber
    {
        return $this->newsletterRepository->findNewsletterSubscribeByEmailAndDomainId($email, $domainId);
    }

    public function getQueryBuilderForQuickSearch(int $selectedDomainId, QuickSearchFormData $searchData): \Doctrine\ORM\QueryBuilder
    {
        return $this->newsletterRepository->getQueryBuilderForQuickSearch($selectedDomainId, $searchData);
    }

    public function getNewsletterSubscriberById(int $id): \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber
    {
        return $this->newsletterRepository->getNewsletterSubscriberById($id);
    }

    public function deleteById(int $id)
    {
        $newsletterSubscriber = $this->getNewsletterSubscriberById($id);

        $this->em->remove($newsletterSubscriber);
        $this->em->flush();
    }
}
