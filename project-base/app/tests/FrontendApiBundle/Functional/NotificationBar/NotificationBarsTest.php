<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\NotificationBar;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class NotificationBarsTest extends GraphQlTestCase
{
    public function testNavigation(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/NotificationBarsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'notificationBars');
        $expectedData = [
            [
                'text' => t('Notification in the bar, notification of a new event.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()),
                'rgbColor' => '#000000',
                'images' => [],
            ],
        ];

        $this->assertSame($expectedData, $responseData);
    }
}
