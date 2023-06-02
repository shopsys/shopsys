<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\NotificationBar;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class NotificationBarsTest extends GraphQlTestCase
{
    public function testNavigation(): void
    {
        $query = '
            query {
                notificationBars {
                   text
                   rgbColor
                   images {
                       position
                       sizes {
                           url
                       }
                   }
                }
            }
        ';

        $jsonExpected = '{
            "data": {
                "notificationBars": [
                    {
                        "text": "' . t('Notification in the bar, notification of a new event.', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getFirstDomainLocale()) . '",
                        "rgbColor": "#000000",
                        "images": []
                    }
                ]
            }
        }';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
