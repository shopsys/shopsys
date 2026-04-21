<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Token;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Token\Exception\ExpiredTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\TokenAuthenticator;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Shopsys\FrontendApiBundle\Model\User\FrontendApiUserProvider;
use Symfony\Component\HttpFoundation\Request;

class TokenAuthenticatorTest extends TestCase
{
    public function testOnAuthenticationFailureReturnsExpiredTokenErrorWithHttpOkStatus(): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator();

        $response = $tokenAuthenticator->onAuthenticationFailure(
            new Request(),
            new ExpiredTokenUserMessageException('Token is expired. Please renew.'),
        );

        $responseData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('expired-token', $responseData['errors'][0]['extensions']['userCode']);
    }

    public function testOnAuthenticationFailureReturnsInvalidTokenErrorWithHttpOkStatus(): void
    {
        $tokenAuthenticator = $this->createTokenAuthenticator();

        $response = $tokenAuthenticator->onAuthenticationFailure(
            new Request(),
            new InvalidTokenUserMessageException('Token is not valid.'),
        );

        $responseData = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('invalid-token', $responseData['errors'][0]['extensions']['userCode']);
    }

    private function createTokenAuthenticator(): TokenAuthenticator
    {
        return new TokenAuthenticator(
            $this->createStub(TokenFacade::class),
            $this->createStub(FrontendApiUserProvider::class),
            $this->createStub(CustomerUserFacade::class),
        );
    }
}
