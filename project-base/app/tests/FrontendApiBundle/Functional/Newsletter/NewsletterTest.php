<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Newsletter;

use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class NewsletterTest extends GraphQlTestCase
{
    private const DEFAULT_USER_EMAIL = 'no-reply@shopsys.com';

    public function testNewsletterSubscribeRegister(): void
    {
        $graphQlType = 'NewsletterSubscribe';
        $response = $this->getResponseContentForQuery($this->getNewsletterSubscribeQuery());

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey($graphQlType, $response['data']);
        $isNewsletterSubscribeSuccess = $response['data'][$graphQlType];
        $this->assertTrue($isNewsletterSubscribeSuccess);
    }

    public function testNewsletterSubscribeWithInvalidEmailRegister(): void
    {
        $response = $this->getResponseContentForQuery($this->getNewsletterSubscribeQuery('no-replyshopsys.com'));

        $this->assertResponseContainsArrayOfExtensionValidationErrors($response);
        $responseData = $this->getErrorsExtensionValidationFromResponse($response);

        $this->assertArrayHasKey('input.email', $responseData);

        $emailError = $responseData['input.email'][0];

        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $expectedViolationMessage = t('Please enter valid email', [], Translator::VALIDATOR_TRANSLATION_DOMAIN, $firstDomainLocale);

        $this->assertArrayHasKey('message', $emailError);
        $this->assertEquals($expectedViolationMessage, $emailError['message']);
    }

    /**
     * @param string $email
     * @return string
     */
    private function getNewsletterSubscribeQuery(string $email = self::DEFAULT_USER_EMAIL): string
    {
        return
            'mutation {
                NewsletterSubscribe(input: {
                    email: "' . $email . '"
                })
            }';
    }
}
