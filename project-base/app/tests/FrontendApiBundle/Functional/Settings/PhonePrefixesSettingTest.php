<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class PhonePrefixesSettingTest extends GraphQlTestCase
{
    public function testGetPhonePrefixesSettings(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PhonePrefixesSettingsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'settings');

        $phonePrefixes = $responseData['phonePrefixes'];

        self::assertGreaterThanOrEqual(2, count($phonePrefixes));

        $firstPrefix = $phonePrefixes[0];
        self::assertArrayHasKey('code', $firstPrefix);
        self::assertArrayHasKey('dialCode', $firstPrefix);
        self::assertArrayHasKey('countryName', $firstPrefix);
        self::assertArrayHasKey('flagEmoji', $firstPrefix);

        self::assertMatchesRegularExpression('/^\+\d+$/', $firstPrefix['dialCode']);
        self::assertSame(2, strlen($firstPrefix['code']));
    }

    public function testDefaultPrefixIsFirst(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/PhonePrefixesSettingsQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, 'settings');

        $phonePrefixes = $responseData['phonePrefixes'];

        self::assertSame('CZ', $phonePrefixes[0]['code']);
        self::assertSame('+420', $phonePrefixes[0]['dialCode']);
    }
}
