<?php

namespace Shopsys\ShopBundle\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;

class NewsletterSubscriberDataFixture extends AbstractReferenceFixture
{
    public const FIRST_DOMAIN_ID = 1;

    /** @var \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade */
    protected $newsletterFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     */
    public function __construct(NewsletterFacade $newsletterFacade)
    {
        $this->newsletterFacade = $newsletterFacade;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityManagerDecorator $manager
     */
    public function load(ObjectManager $manager)
    {
        $newsletterSubscribersData = $this->getEmailData();

        foreach ($newsletterSubscribersData as $email) {
            $this->newsletterFacade->addSubscribedEmail($email, self::FIRST_DOMAIN_ID);
        }
    }

    /**
     * @return string[]
     */
    protected function getEmailData()
    {
        return [
            'james.black@no-reply.com',
            'johny.good@no-reply.com',
            'andrew.mathewson@no-reply.com',
            'vitek@shopsys.com',
        ];
    }
}
