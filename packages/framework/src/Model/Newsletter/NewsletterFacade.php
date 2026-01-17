<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Newsletter;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class NewsletterFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly NewsletterRepository $newsletterRepository,
        protected readonly NewsletterSubscriberFactory $newsletterSubscriberFactory,
        protected readonly ClockInterface $clock,
    ) {
    }

    public function addSubscribedEmailIfNotExists(string $email, int $domainId): void
    {
        if ($this->newsletterRepository->existsSubscribedEmail($email, $domainId)) {
            return;
        }

        $newsletterSubscriber = $this->newsletterSubscriberFactory->create(
            $email,
            $this->clock->now(),
            $domainId,
        );
        $this->em->persist($newsletterSubscriber);
        $this->em->flush();
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\Internal\Hydration\IterableResult
     */
    public function getAllEmailsDataIteratorByDomainId($domainId)
    {
        return $this->newsletterRepository->getAllEmailsDataIteratorByDomainId($domainId);
    }

    /**
     * @param string $email
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriber|null
     */
    public function findNewsletterSubscriberByEmailAndDomainId($email, $domainId)
    {
        return $this->newsletterRepository->findNewsletterSubscribeByEmailAndDomainId($email, $domainId);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryBuilderForQuickSearch(int $selectedDomainId, QuickSearchFormData $searchData)
    {
        return $this->newsletterRepository->getQueryBuilderForQuickSearch($selectedDomainId, $searchData);
    }

    public function getNewsletterSubscriberById(int $id): NewsletterSubscriber
    {
        $newsletterSubscriber = $this->newsletterRepository->findNewsletterSubscriberById($id);

        if ($newsletterSubscriber === null) {
            throw new NewsletterSubscriberNotFoundException();
        }

        return $newsletterSubscriber;
    }

    public function delete(NewsletterSubscriber $newsletterSubscriber): void
    {
        $this->em->remove($newsletterSubscriber);
        $this->em->flush();
    }

    public function deleteSubscribedEmailIfExists(string $email, int $domainId): void
    {
        $newsletterSubscriber = $this->newsletterRepository->findNewsletterSubscribeByEmailAndDomainId($email, $domainId);

        if ($newsletterSubscriber === null) {
            return;
        }

        $this->delete($newsletterSubscriber);
    }

    public function isSubscribed(CustomerUser $customerUser): bool
    {
        return $this->findNewsletterSubscriberByEmailAndDomainId($customerUser->getEmail(), $customerUser->getDomainId()) !== null;
    }
}
