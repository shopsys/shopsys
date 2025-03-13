<?php

declare(strict_types=1);

namespace App\DataFixtures\Demo;

use Doctrine\Persistence\ObjectManager;
use Override;
use Shopsys\FrameworkBundle\Component\DataFixture\AbstractReferenceFixture;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarDataFactory;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarFacade;

class NotificationBarDataFixture extends AbstractReferenceFixture
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarFacade $notificationBarFacade
     * @param \Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBarDataFactory $notificationBarDataFactory
     * @param \Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper $dateTimeHelper
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     */
    public function __construct(
        private readonly NotificationBarFacade $notificationBarFacade,
        private readonly NotificationBarDataFactory $notificationBarDataFactory,
        private readonly DateTimeHelper $dateTimeHelper,
        private readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
    ) {
    }

    /**
     * @param \Doctrine\Persistence\ObjectManager $manager
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        foreach ($this->domainsForDataFixtureProvider->getAllowedDemoDataDomains() as $domainConfig) {
            $notificationBarData = $this->notificationBarDataFactory->create();

            $notificationBarData->domainId = $domainConfig->getId();
            $notificationBarData->text = t('Notification in the bar, notification of a new event.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $domainConfig->getLocale());
            $notificationBarData->validityFrom = $this->dateTimeHelper->createUtcDateTimeByTimeZoneAndString('today midnight', $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainConfig->getId()));
            $notificationBarData->validityTo = $this->dateTimeHelper->createUtcDateTimeByTimeZoneAndString('+7 days midnight', $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId($domainConfig->getId()));
            $notificationBarData->rgbColor = '#000000';
            $notificationBarData->hidden = false;

            $this->notificationBarFacade->create($notificationBarData);
        }
    }
}
