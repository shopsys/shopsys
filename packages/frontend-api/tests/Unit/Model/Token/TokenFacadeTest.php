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
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrontendApiBundle\Model\Token\Exception\ExpiredTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\Exception\NotVerifiedTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\JwtConfigurationFactory;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class TokenFacadeTest extends TestCase
{
    /**
     * @dataProvider tokensDataProvider
     * @param \Lcobucci\JWT\UnencryptedToken $token
     * @param class-string|null $exceptionClass
     */
    public function testTokenValidation(UnencryptedToken $token, ?string $exceptionClass): void
    {
        $tokenFacade = $this->createTokenFacade();

        if ($exceptionClass !== null) {
            $this->expectException($exceptionClass);
        }
        $tokenFacade->validateToken($token);
    }

    /**
     * @return iterable
     */
    public function tokensDataProvider(): iterable
    {
        $builderTemplate = (new Builder(new JoseEncoder(), ChainedFormatter::default()))
            ->issuedBy('http://webserver:8080')
            ->permittedFor('http://webserver:8080')
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable('- 10 minutes'))
            ->expiresAt(new DateTimeImmutable('+ 10 minutes'));

        $jwtConfiguration = $this->createJwtConfiguration();
        $signer = $jwtConfiguration->signer();
        $privateKey = $jwtConfiguration->signingKey();

        $builder = clone $builderTemplate;
        yield [
            $builder->getToken($signer, $privateKey),
            null,
        ];

        $builder = clone $builderTemplate;
        yield [
            $builder
                ->issuedBy('http://another-server:8080')
                ->getToken($signer, $privateKey),
            InvalidTokenUserMessageException::class,
        ];

        $builder = clone $builderTemplate;
        yield [
            $builder->getToken($signer, InMemory::file(__DIR__ . '/testKeys/invalid-private.key')),
            NotVerifiedTokenUserMessageException::class,
        ];

        $builder = clone $builderTemplate;
        yield [
            $builder
                ->expiresAt(new DateTimeImmutable('- 5 minutes'))
                ->getToken($signer, $privateKey),
            ExpiredTokenUserMessageException::class,
        ];
    }

    /**
     * @return \Shopsys\FrontendApiBundle\Model\Token\TokenFacade
     */
    private function createTokenFacade(): TokenFacade
    {
        $domain = $this->createDomain();

        $customerUserFacade = $this->createMock(CustomerUserFacade::class);

        $jwtConfiguration = $this->createJwtConfiguration();

        return new TokenFacade(
            $domain,
            $customerUserFacade,
            $jwtConfiguration
        );
    }

    /**
     * @return \Lcobucci\JWT\Configuration
     */
    private function createJwtConfiguration(): Configuration
    {
        $domain = $this->createDomain();
        $parameterBag = new ParameterBag(['shopsys.frontend_api.keys_filepath' => __DIR__ . '/testKeys']);

        return (new JwtConfigurationFactory($parameterBag, $domain))->create();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private function createDomain(): Domain
    {
        $domainConfig = new DomainConfig(1, 'http://webserver:8080', 'domain', 'en');
        $setting = $this->createMock(Setting::class);

        $domain = new Domain([$domainConfig], $setting);
        $domain->switchDomainById(1);

        return $domain;
    }
}
