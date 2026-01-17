<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Token;

use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\UnencryptedToken;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Token\Exception\ExpiredTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\NotVerifiedTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\JwtConfigurationProvider;
use Shopsys\FrontendApiBundle\Model\Token\TokenCustomerUserTransformer;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Symfony\Component\Clock\DatePoint;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class TokenFacadeTest extends TestCase
{
    /**
     * @param class-string|null $exceptionClass
     */
    #[DataProvider('tokensDataProvider')]
    public function testTokenValidation(
        ?string $issuedBy,
        ?InMemory $privateKey,
        ?DateTimeImmutable $expiresAt,
        ?string $exceptionClass,
    ): void {
        $token = $this->createToken($issuedBy, $privateKey, $expiresAt);
        $tokenFacade = $this->createTokenFacade();

        if ($exceptionClass !== null) {
            $this->expectException($exceptionClass);
        }
        $tokenFacade->validateToken($token);
    }

    protected function createToken(
        ?string $issuedBy,
        ?InMemory $privateKey,
        ?DateTimeImmutable $expiresAt,
    ): UnencryptedToken {
        $builder = (new Builder(new JoseEncoder(), ChainedFormatter::default()))
            ->issuedBy(DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL)
            ->permittedFor(DomainConfigHelper::DEFAULT_EXAMPLE_COM_BASE_URL)
            ->issuedAt(new DatePoint())
            ->canOnlyBeUsedAfter((new DatePoint())->modify('- 10 minutes'))
            ->expiresAt((new DatePoint())->modify('+ 10 minutes'));

        $jwtConfiguration = $this->createJwtConfiguration();
        $signer = $jwtConfiguration->signer();

        if ($privateKey === null) {
            $privateKey = $jwtConfiguration->signingKey();
        }

        if ($issuedBy !== null) {
            $builder = $builder->issuedBy($issuedBy);
        }

        if ($expiresAt !== null) {
            $builder = $builder->expiresAt($expiresAt);
        }

        return $builder->getToken($signer, $privateKey);
    }

    public static function tokensDataProvider(): iterable
    {
        yield [
            'issuedBy' => null,
            'privateKey' => null,
            'expiresAt' => null,
            'exceptionClass' => null,
        ];

        yield [
            'issuedBy' => 'http://another-server:8080',
            'privateKey' => null,
            'expiresAt' => null,
            'exceptionClass' => InvalidTokenUserMessageException::class,
        ];

        yield [
            'issuedBy' => null,
            'privateKey' => InMemory::file(__DIR__ . '/testKeys/invalid-private.key'),
            'expiresAt' => null,
            'exceptionClass' => NotVerifiedTokenUserMessageException::class,
        ];

        yield [
            'issuedBy' => null,
            'privateKey' => null,
            'expiresAt' => (new DatePoint())->modify('- 5 minutes'),
            'exceptionClass' => ExpiredTokenUserMessageException::class,
        ];
    }

    private function createTokenFacade(): TokenFacade
    {
        $domain = $this->createDomain();

        $customerUserFacade = $this->createMock(CustomerUserFacade::class);
        $jwtConfigurationProvider = $this->getMockBuilder(JwtConfigurationProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $jwtConfigurationProvider->method('getConfiguration')
            ->willReturn($this->createJwtConfiguration());

        $clock = $this->createMock(ClockInterface::class);
        $clock->method('now')->willReturn(new DatePoint());

        return new TokenFacade(
            $domain,
            $customerUserFacade,
            $jwtConfigurationProvider,
            new TokenCustomerUserTransformer(),
            $clock,
        );
    }

    private function createJwtConfiguration(): Configuration
    {
        $domain = $this->createDomain();
        $parameterBag = new ParameterBag(['shopsys.frontend_api.keys_filepath' => __DIR__ . '/testKeys']);

        return (new JwtConfigurationProvider($parameterBag, $domain))->getConfiguration();
    }

    private function createDomain(): Domain
    {
        $domainConfig = DomainConfigHelper::getDomainConfig();
        $setting = $this->createMock(Setting::class);
        $administratorFacadeMock = $this->createMock(AdministratorFacade::class);

        $domain = new Domain(
            [$domainConfig],
            $setting,
            $administratorFacadeMock,
        );

        $domain->switchDomainById(1);

        return $domain;
    }
}
