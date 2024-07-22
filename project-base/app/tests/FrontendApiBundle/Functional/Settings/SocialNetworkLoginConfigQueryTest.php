<?php

declare(strict_types=1);

namespace FrontendApiBundle\Functional\Settings;

use Shopsys\FrontendApiBundle\Model\Customer\User\LoginType\LoginTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class SocialNetworkLoginConfigQueryTest extends GraphQlTestCase
{
    public function testSocialNetworkLoginConfigQuery(): void
    {
        $graphQlType = 'settings';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/SocialNetworkLoginConfigQuery.graphql');
        $responseData = $this->getResponseDataForGraphQlType($response, $graphQlType);
        $expectedData = [
            'socialNetworkLoginConfig' => [
                LoginTypeEnum::GOOGLE,
                LoginTypeEnum::FACEBOOK,
                LoginTypeEnum::SEZNAM,
            ],
        ];

        self::assertSame($expectedData, $responseData);
    }
}
