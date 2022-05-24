<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PricingSettingTest extends GraphQlTestCase
{
    public function testGetPricingSettings(): void
    {
        $query = '
            query {
                settings {
                    pricing {
                        defaultCurrencyCode
                        minimumFractionDigits
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $data = $this->getResponseDataForGraphQlType($response, 'settings');

        self::assertEquals('CZK', $data['pricing']['defaultCurrencyCode']);
        self::assertEquals(2, $data['pricing']['minimumFractionDigits']);
    }
}
