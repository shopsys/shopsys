<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use App\Model\NotificationBar\NotificationBarDataFactory;
use App\Model\NotificationBar\NotificationBarFacade;
use DateTime;
use Doctrine\Persistence\ObjectManager;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Translation\Translator;

class NotificationBarDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \App\Model\NotificationBar\NotificationBarFacade $notificationBarFacade
     * @param \App\Model\NotificationBar\NotificationBarDataFactory $notificationBarDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly NotificationBarFacade $notificationBarFacade,
        private readonly NotificationBarDataFactory $notificationBarDataFactory,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            $notificationBarData = $this->notificationBarDataFactory->create();

            $notificationBarData->domainId = $domainConfig->getId();
            $notificationBarData->text = t('Notification in the bar, notification of a new event.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $notificationBarData->validityFrom = new DateTime('today midnight');
            $notificationBarData->validityTo = new DateTime('+7 days midnight');
            $notificationBarData->rgbColor = '#000000';
            $notificationBarData->hidden = false;

            $this->notificationBarFacade->create($notificationBarData);
        }
    }
}
