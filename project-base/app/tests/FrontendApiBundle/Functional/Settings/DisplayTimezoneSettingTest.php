<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class DisplayTimezoneSettingTest extends GraphQlTestCase
{
    public function testGetDisplayTimezoneSettings(): void
    {
        $graphQlType = 'settings';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/DisplayTimezoneSettingsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);
        $expectedData = ['displayTimezone' => 'Europe/Prague'];

        self::assertSame($expectedData, $responseData);
    }
}
