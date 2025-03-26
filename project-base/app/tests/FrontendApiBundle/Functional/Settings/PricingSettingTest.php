<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
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
                        sellingPriceType
                    }
                }
            }
        ';

        $response = $this->getResponseContentForQuery($query);
        $data = $this->getResponseDataForGraphQlType($response, 'settings');

        $firstDomainCurrency = $this->getFirstDomainCurrency();

        self::assertEquals($firstDomainCurrency->getCode(), $data['pricing']['defaultCurrencyCode']);
        self::assertEquals($firstDomainCurrency->getMinFractionDigits(), $data['pricing']['minimumFractionDigits']);
        self::assertEquals($this->pricingSetting->getSellingPriceType() === PricingSetting::PRICE_TYPE_WITH_VAT ? 'WITH_VAT' : 'WITHOUT_VAT', $data['pricing']['sellingPriceType']);
    }
}
