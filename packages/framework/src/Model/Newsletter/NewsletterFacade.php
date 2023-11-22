<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Newsletter;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;

class NewsletterFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterRepository $newsletterRepository
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriberFactoryInterface $newsletterSubscriberFactory
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NewsletterRepository $newsletterRepository,
        protected readonly NewsletterSubscriberFactoryInterface $newsletterSubscriberFactory,
    ) {
    }

    /**
     * @param string $email
     * @param int $domainId
     */
    public function addSubscribedEmail($email, $domainId): void
    {
        if ($this->newsletterRepository->existsSubscribedEmail($email, $domainId)) {
            return;
        }

        $newsletterSubscriber = $this->newsletterSubscriberFactory->create(
            $email,
            new DateTimeImmutable(),
            $domainId,
        );
        $this->em->persist($newsletterSubscriber);
        $this->em->flush();
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getAllEmailsDataIteratorByDomainId($domainId): \Doctrine\ORM\Internal\Hydration\IterableResult
    {
        return $this->newsletterRepository->getAllEmailsDataIteratorByDomainId($domainId);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber|null
     */
    public function findNewsletterSubscriberByEmailAndDomainId($email, $domainId): ?\Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber
    {
        return $this->newsletterRepository->findNewsletterSubscribeByEmailAndDomainId($email, $domainId);
    }

    /**
     * @param int $selectedDomainId
     * @param \Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData $searchData
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderForQuickSearch(int $selectedDomainId, QuickSearchFormData $searchData): \Doctrine\ORM\QueryBuilder
    {
        return $this->newsletterRepository->getQueryBuilderForQuickSearch($selectedDomainId, $searchData);
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber
     */
    public function getNewsletterSubscriberById(int $id): \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber
    {
        return $this->newsletterRepository->getNewsletterSubscriberById($id);
    }

    /**
     * @param int $id
     */
    public function deleteById(int $id): void
    {
        $newsletterSubscriber = $this->getNewsletterSubscriberById($id);

        $this->em->remove($newsletterSubscriber);
        $this->em->flush();
    }
}
