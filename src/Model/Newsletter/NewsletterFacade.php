<?php

declare(strict_types=1);

namespace App\Model\Newsletter;

use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade as BaseNewsletterFacade;

/**
 * @method \App\Model\Newsletter\NewsletterSubscriber|null findNewsletterSubscriberByEmailAndDomainId(string $email, int $domainId)
 * @method \App\Model\Newsletter\NewsletterSubscriber getNewsletterSubscriberById(int $id)
 * @property \App\Model\Newsletter\NewsletterRepository $newsletterRepository
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Newsletter\NewsletterRepository $newsletterRepository, \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterSubscriberFactoryInterface $newsletterSubscriberFactory)
 */
class NewsletterFacade extends BaseNewsletterFacade
{
    /**
     * @param string $email
     * @param int $domainId
     */
    public function addSubscribedEmail($email, $domainId): void
    {
        /**
         * @var \App\Model\Newsletter\NewsletterSubscriber|null
         */
        $newsletterSubscriber = $this->newsletterRepository->findNewsletterSubscribeByEmailAndDomainId($email, $domainId);

        if ($newsletterSubscriber === null) {
            $newsletterSubscriber = $this->newsletterSubscriberFactory->create($email, new \DateTimeImmutable(), $domainId);
        } else {
            $newsletterSubscriber->setDeleted(false);
        }

        $this->em->persist($newsletterSubscriber);
        $this->em->flush();
    }

    /**
     * @param int $id
     */
    public function deleteById(int $id): void
    {
        /**
         * @var \App\Model\Newsletter\NewsletterSubscriber
         */
        $newsletterSubscriber = $this->getNewsletterSubscriberById($id);
        $newsletterSubscriber->setDeleted(true);
        $newsletterSubscriber->setUpdatedAt(\DateTimeImmutable::createFromFormat('U', (string)time()));
        $this->em->persist($newsletterSubscriber);
        $this->em->flush();
    }
}
