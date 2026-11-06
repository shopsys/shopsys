<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Login;

use Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator;
use Symfony\Component\BrowserKit\Cookie;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class CookieAuthenticationTest extends GraphQlTestCase
{
    private const string USER_EMAIL = 'no-reply@shopsys.com';

    private const string USER_PASSWORD = 'user123';

    /**
     * @inject
     */
    private TokenAuthenticator $tokenAuthenticator;

    public function testAccessTokenCookieAuthenticatesSameOriginPostWithDomainIdHeader(): void
    {
        $this->setAccessTokenCookie();
        $this->configureCurrentClient(null, null, $this->getCookieAuthenticationClientOptions());

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/CurrentCustomerUserQuery.graphql');

        $this->assertSame(self::USER_EMAIL, $this->getResponseDataForGraphQlType($response, 'currentCustomerUser')['email']);
    }

    public function testAccessTokenCookieDoesNotAuthenticateFromForeignOrigin(): void
    {
        $this->setAccessTokenCookie();
        $clientOptions = $this->getCookieAuthenticationClientOptions();
        $clientOptions['HTTP_ORIGIN'] = 'https://evil.example';
        $this->configureCurrentClient(null, null, $clientOptions);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/CurrentCustomerUserQuery.graphql');

        $this->assertNull($response['data']['currentCustomerUser']);
    }

    private function setAccessTokenCookie(): void
    {
        $responseData = $this->getResponseContentForGql(__DIR__ . '/graphql/LoginMutation.graphql', [
            'email' => self::USER_EMAIL,
            'password' => self::USER_PASSWORD,
        ]);
        $accessToken = $responseData['data']['Login']['tokens']['accessToken'];

        self::getCurrentClient()->getCookieJar()->set(new Cookie(
            $this->tokenAuthenticator->getAccessTokenCookieNameForDomainId($this->domain->getId()),
            $accessToken,
        ));
    }

    /**
     * @return array<string, string>
     */
    private function getCookieAuthenticationClientOptions(): array
    {
        return [
            'CONTENT_TYPE' => 'application/graphql',
            'HTTP_X-Domain-Id' => (string)$this->domain->getId(),
            'HTTP_ORIGIN' => rtrim($this->domain->getUrl(), '/'),
        ];
    }
}
