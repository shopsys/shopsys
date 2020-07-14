<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Component\Domain\Domain;
use App\Model\NotificationBar\NotificationBarDataFactory;
use App\Model\NotificationBar\NotificationBarFacade;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;

class NotificationBarDataFixture extends AbstractReferenceFixture
{
    /**
     * @var \App\Model\NotificationBar\NotificationBarFacade
     */
    private $notificationBarFacade;

    /**
     * @var \App\Model\NotificationBar\NotificationBarDataFactory
     */
    private $notificationBarDataFactory;

    /**
     * @param \App\Model\NotificationBar\NotificationBarFacade $notificationBarFacade
     * @param \App\Model\NotificationBar\NotificationBarDataFactory $notificationBarDataFactory
     */
    public function __construct(NotificationBarFacade $notificationBarFacade, NotificationBarDataFactory $notificationBarDataFactory)
    {
        $this->notificationBarFacade = $notificationBarFacade;
        $this->notificationBarDataFactory = $notificationBarDataFactory;
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $notificationBarData = $this->notificationBarDataFactory->create();

        $notificationBarData->domainId = Domain::FIRST_DOMAIN_ID;
        $notificationBarData->text = 'Notifikace v liště, upozornění na novou akci.';
        $notificationBarData->validityFrom = new DateTime('today midnight');
        $notificationBarData->validityTo = new DateTime('+7 days midnight');
        $notificationBarData->rgbColor = '#000000';
        $notificationBarData->hidden = false;

        $this->notificationBarFacade->create($notificationBarData);
    }
}
