<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;

class NewsletterSubscriberDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     */
    public function __construct(
        private readonly NewsletterFacade $newsletterFacade,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomainIds() as $domainId) {
            if ($domainId === Domain::SECOND_DOMAIN_ID) {
                $newsletterSubscribersData = $this->getDistinctEmailData();
            } else {
                $newsletterSubscribersData = $this->getDefaultEmailData();
            }

            foreach ($newsletterSubscribersData as $email) {
                $this->newsletterFacade->addSubscribedEmailIfNotExists($email, $domainId);
            }
        }
    }

    /**
     * @return string[]
     */
    private function getDefaultEmailData(): array
    {
        return [
            'no-reply@shopsys.com',
            'james.black@no-reply.com',
            'johny.good@no-reply.com',
            'andrew.mathewson@no-reply.com',
            'vitek@shopsys.com',
        ];
    }

    /**
     * @return string[]
     */
    private function getDistinctEmailData(): array
    {
        return [
            'anna.anina@no-reply.com',
            'jonathan.anderson@no-reply.com',
            'peter.parkson@no-reply.com',
        ];
    }
}
