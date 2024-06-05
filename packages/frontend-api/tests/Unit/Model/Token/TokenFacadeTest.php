<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Token;

use DateTimeImmutable;
use DateTimeZone;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Token\Builder;
use Lcobucci\JWT\Token\Plain;
use PHPUnit\Framework\Attributes\DataProvider;
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
     * @param string|null $issuedBy
     * @param \Lcobucci\JWT\Signer\Key\InMemory|null $privateKey
     * @param \DateTimeImmutable|null $expiresAt
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

    /**
     * @param string|null $issuedBy
     * @param \Lcobucci\JWT\Signer\Key\InMemory|null $privateKey
     * @param \DateTimeImmutable|null $expiresAt
     * @return \Lcobucci\JWT\Token\Plain
     */
    protected function createToken(?string $issuedBy, ?InMemory $privateKey, ?DateTimeImmutable $expiresAt): Plain
    {
        $builder = (new Builder(new JoseEncoder(), ChainedFormatter::default()))
            ->issuedBy('http://webserver:8080')
            ->permittedFor('http://webserver:8080')
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable('- 10 minutes'))
            ->expiresAt(new DateTimeImmutable('+ 10 minutes'));

        $jwtConfiguration = $this->createJwtConfiguration();
        $signer = $jwtConfiguration->signer();

        if ($privateKey === null) {
            $privateKey = $jwtConfiguration->signingKey();
        }

        if ($issuedBy !== null) {
            $builder->issuedBy($issuedBy);
        }

        if ($expiresAt !== null) {
            $builder->expiresAt($expiresAt);
        }

        return $builder->getToken($signer, $privateKey);
    }

    /**
     * @return iterable
     */
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
            'expiresAt' => new DateTimeImmutable('- 5 minutes'),
            'exceptionClass' => ExpiredTokenUserMessageException::class,
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
            $jwtConfiguration,
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
        $defaultTimeZone = new DateTimeZone('Europe/Prague');
        $domainConfig = new DomainConfig(1, 'http://webserver:8080', 'domain', 'en', $defaultTimeZone);
        $setting = $this->createMock(Setting::class);

        $domain = new Domain([$domainConfig], $setting);
        $domain->switchDomainById(1);

        return $domain;
    }
}
