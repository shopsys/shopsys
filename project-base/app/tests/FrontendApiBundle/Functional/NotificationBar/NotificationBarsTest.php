<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\NotificationBar;

use App\DataFixtures\Demo\NotificationBarDataFixture;
use Shopsys\FrameworkBundle\Component\DateTimeHelper\DateTimeHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\NotificationBar\NotificationBar;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class NotificationBarsTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private DisplayTimeZoneProviderInterface $displayTimeZoneProvider;

    /**
     * @inject
     */
    private DateTimeHelper $dateTimeHelper;

    public function testNavigation(): void
    {
        $notificationBar = $this->getReferenceForDomain(NotificationBarDataFixture::NOTIFICATION_BAR_PREFIX, Domain::FIRST_DOMAIN_ID, NotificationBar::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/NotificationBarsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'notificationBars');
        $expectedData = [
            [
                'uuid' => $notificationBar->getUuid(),
                'text' => t('Notification in the bar, notification of a new event.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'rgbColor' => '#000000',
                'images' => [],
                'validityFrom' => $this->dateTimeHelper->createUtcDateTimeByTimeZoneAndString('today midnight', $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId(Domain::FIRST_DOMAIN_ID))->format(DATE_ATOM),
                'validityTo' => $this->dateTimeHelper->createUtcDateTimeByTimeZoneAndString('+7 days midnight', $this->displayTimeZoneProvider->getDisplayTimeZoneByDomainId(Domain::FIRST_DOMAIN_ID))->format(DATE_ATOM),
            ],
        ];

        $this->assertSame($expectedData, $responseData);
    }
}
