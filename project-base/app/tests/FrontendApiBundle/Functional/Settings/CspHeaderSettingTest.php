<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class CspHeaderSettingTest extends GraphQlTestCase
{
    public function testGetCspHeaderSetting(): void
    {
        $graphQlType = 'settings';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CspHeaderSettingsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);
        $expectedData = ['cspHeader' => $this->setting->get(Setting::CSP_HEADER)];

        self::assertSame($expectedData, $responseData);
    }
}
