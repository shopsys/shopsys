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

        $firstDomainCurrency = $this->getFirstDomainCurrency();

        self::assertEquals($firstDomainCurrency->getCode(), $data['pricing']['defaultCurrencyCode']);
        self::assertEquals($firstDomainCurrency->getMinFractionDigits(), $data['pricing']['minimumFractionDigits']);
    }
}
