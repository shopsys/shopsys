<?php

namespace Shopsys\FrameworkBundle\DataFixtures\DemoMultidomain;

use Doctrine\Common\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;

class NewsletterSubscriberDataFixture extends AbstractReferenceFixture
{
    const SECOND_DOMAIN_ID = 2;

    public function __construct(NewsletterFacade $newsletterFacade)
    {
        $this->newsletterFacade = $newsletterFacade;
    }

    /** @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade */
    private $newsletterFacade;

    public function load(ObjectManager $manager): void
    {
        $newsletterSubscribersData = $this->getEmailData();

        foreach ($newsletterSubscribersData as $email) {
            $this->newsletterFacade->addSubscribedEmail($email, self::SECOND_DOMAIN_ID);
        }
    }

    /**
     * @return string[]
     */
    private function getEmailData(): array
    {
        return [
            'anna.anina@no-reply.com',
            'jonathan.anderson@no-reply.com',
            'peter.parkson@no-reply.com',
        ];
    }
}
