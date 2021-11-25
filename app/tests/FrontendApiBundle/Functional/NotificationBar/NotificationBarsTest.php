<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\NotificationBar;

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
                        "text": "' . t('Notifikace v liště, upozornění na novou akci.', [], 'dataFixtures', $this->getFirstDomainLocale()) . '",
                        "rgbColor": "#000000",
                        "images": []
                    }
                ]
            }
        }';

        $this->assertQueryWithExpectedJson($query, $jsonExpected);
    }
}
