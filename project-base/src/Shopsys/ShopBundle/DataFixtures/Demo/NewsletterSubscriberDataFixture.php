<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;

class NewsletterSubscriberDataFixture extends AbstractReferenceFixture
{
    /** @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade */
    protected $newsletterFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(NewsletterFacade $newsletterFacade, Domain $domain)
    {
        $this->newsletterFacade = $newsletterFacade;
        $this->domain = $domain;
    }

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $domainId = $domainConfig->getId();

            if ($domainId === Domain::SECOND_DOMAIN_ID) {
                $newsletterSubscribersData = $this->getDistinctEmailData();
            } else {
                $newsletterSubscribersData = $this->getDefaultEmailData();
            }
            foreach ($newsletterSubscribersData as $email) {
                $this->newsletterFacade->addSubscribedEmail($email, $domainId);
            }
        }
    }

    /**
     * @return string[]
     */
    protected function getDefaultEmailData(): array
    {
        return [
            'james.black@no-reply.com',
            'johny.good@no-reply.com',
            'andrew.mathewson@no-reply.com',
            'vitek@shopsys.com',
        ];
    }

    /**
     * @return string[]
     */
    protected function getDistinctEmailData(): array
    {
        return [
            'anna.anina@no-reply.com',
            'jonathan.anderson@no-reply.com',
            'peter.parkson@no-reply.com',
        ];
    }
}
