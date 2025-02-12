<?php

declare(strict_types=1);

namespace FrontendApiBundle\Functional\Settings;

use Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationLocaleHelper;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class HeurekaEnabledSettingTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    protected HeurekaShopCertificationLocaleHelper $heurekaShopCertificationLocaleHelper;

    public function testGetHeurekaEnabledSettings(): void
    {
        $graphQlType = 'settings';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/HeurekaEnabledSettingsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);
        $expectedData = ['heurekaEnabled' => $this->heurekaShopCertificationLocaleHelper->isDomainLocaleSupported($this->getFirstDomainLocale())];

        self::assertSame($expectedData, $responseData);
    }
}
