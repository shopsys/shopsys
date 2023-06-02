<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Newsletter;

use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class NewsletterSubscribeTest extends GraphQlTestCase
{
    private const DEFAULT_USER_EMAIL = 'no-reply@shopsys.com';

    public function testNewsletterSubscribeRegister(): void
    {
        $graphQlType = 'NewsletterSubscribe';
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/NewsletterSubscribeMutation.graphql', [
            'email' => self::DEFAULT_USER_EMAIL,
        ]);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey($graphQlType, $response['data']);
        $isNewsletterSubscribeSuccess = $response['data'][$graphQlType];
        $this->assertTrue($isNewsletterSubscribeSuccess);
    }

    public function testNewsletterSubscribeWithInvalidEmailRegister(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/NewsletterSubscribeMutation.graphql', [
            'email' => 'no-replyshopsys.com',
        ]);

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $responseData = $this->getErrorsExtensionValidationFromResponse($response);

        $this->assertArrayHasKey('input.email', $responseData);

        $emailError = $responseData['input.email'][0];

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedViolationMessage = t('Please enter valid email', [], 'validators', $firstDomainLocale);

        $this->assertArrayHasKey('message', $emailError);
        $this->assertEquals($expectedViolationMessage, $emailError['message']);
    }
}
