<?php

declare(strict_types=1);

namespace FrontendApiBundle\Functional\Settings;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class HeurekaEnabledSettingTest extends GraphQlTestCase
{
    public function testGetHeurekaEnabledSettings(): void
    {
        $graphQlType = 'settings';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/HeurekaEnabledSettingsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);
        $expectedData = ['heurekaEnabled' => false];

        self::assertSame($expectedData, $responseData);
    }
}
