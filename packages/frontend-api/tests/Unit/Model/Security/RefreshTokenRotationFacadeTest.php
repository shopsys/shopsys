<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Security;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserRefreshTokenChainFacade;
use Shopsys\FrontendApiBundle\Model\Security\RefreshTokenRedisCacheFacade;
use Shopsys\FrontendApiBundle\Model\Security\TokensData;
use Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Shopsys\FrontendApiBundle\Model\Token\TokenFacade;

final class RefreshTokenRotationFacadeTest extends TestCase
{
    private const string SECRET_CHAIN = 'secret-chain';

    public function testRecentlyRotatedRefreshTokenReturnsCachedTokensWithinGracePeriod(): void
    {
        $cachedTokensData = $this->createTokensData('cached-access-token', 'cached-refresh-token');
        $refreshTokenRedisCacheFacadeMock = $this->createMock(RefreshTokenRedisCacheFacade::class);
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('findCachedTokensData')
            ->with(self::SECRET_CHAIN)
            ->willReturn($cachedTokensData);
        $refreshTokenRedisCacheFacadeMock->expects($this->never())->method('acquireLock');

        $refreshTokenRotationFacade = $this->createRefreshTokenRotationFacade($refreshTokenRedisCacheFacadeMock);
        $tokensData = $refreshTokenRotationFacade->getTokensDataOrGenerateForTest(
            $this->createStub(CustomerUser::class),
            self::SECRET_CHAIN,
            'device-id',
        );

        $this->assertSame($cachedTokensData->accessToken, $tokensData->accessToken);
        $this->assertSame($cachedTokensData->refreshToken, $tokensData->refreshToken);
    }

    public function testGeneratedTokensDataAreStoredWhileLockIsHeld(): void
    {
        $generatedTokensData = $this->createTokensData('generated-access-token', 'generated-refresh-token');
        $refreshTokenRedisCacheFacadeMock = $this->createMock(RefreshTokenRedisCacheFacade::class);
        $refreshTokenRedisCacheFacadeMock->expects($this->exactly(2))
            ->method('findCachedTokensData')
            ->with(self::SECRET_CHAIN)
            ->willReturn(null);
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('acquireLock')
            ->with(self::SECRET_CHAIN, $this->isString())
            ->willReturn(true);
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('saveCachedTokensData')
            ->with(
                self::SECRET_CHAIN,
                $this->identicalTo($generatedTokensData),
            );
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('releaseLock')
            ->with(self::SECRET_CHAIN, $this->isString());

        $refreshTokenRotationFacade = $this->createRefreshTokenRotationFacade(
            $refreshTokenRedisCacheFacadeMock,
            fn (): TokensData => $generatedTokensData,
        );
        $tokensData = $refreshTokenRotationFacade->getTokensDataOrGenerateForTest(
            $this->createStub(CustomerUser::class),
            self::SECRET_CHAIN,
            'device-id',
        );

        $this->assertSame($generatedTokensData, $tokensData);
    }

    public function testCachedTokensDataAreReturnedWhenLockIsAcquiredAfterAnotherRequestStoredCache(): void
    {
        $cachedTokensData = $this->createTokensData('cached-access-token', 'cached-refresh-token');
        $refreshTokenRedisCacheFacadeMock = $this->createMock(RefreshTokenRedisCacheFacade::class);
        $refreshTokenRedisCacheFacadeMock->expects($this->exactly(2))
            ->method('findCachedTokensData')
            ->with(self::SECRET_CHAIN)
            ->willReturnOnConsecutiveCalls(null, $cachedTokensData);
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('acquireLock')
            ->with(self::SECRET_CHAIN, $this->isString())
            ->willReturn(true);
        $refreshTokenRedisCacheFacadeMock->expects($this->never())->method('saveCachedTokensData');
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('releaseLock')
            ->with(self::SECRET_CHAIN, $this->isString());

        $refreshTokenRotationFacade = $this->createRefreshTokenRotationFacade($refreshTokenRedisCacheFacadeMock);
        $tokensData = $refreshTokenRotationFacade->getTokensDataOrGenerateForTest(
            $this->createStub(CustomerUser::class),
            self::SECRET_CHAIN,
            'device-id',
        );

        $this->assertSame($cachedTokensData->accessToken, $tokensData->accessToken);
        $this->assertSame($cachedTokensData->refreshToken, $tokensData->refreshToken);
    }

    public function testCachedTokensDataAreReturnedWhenAnotherRequestHoldsLock(): void
    {
        $cachedTokensData = $this->createTokensData('cached-access-token', 'cached-refresh-token');
        $refreshTokenRedisCacheFacadeMock = $this->createMock(RefreshTokenRedisCacheFacade::class);
        $refreshTokenRedisCacheFacadeMock->expects($this->exactly(2))
            ->method('findCachedTokensData')
            ->with(self::SECRET_CHAIN)
            ->willReturnOnConsecutiveCalls(null, $cachedTokensData);
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('acquireLock')
            ->with(self::SECRET_CHAIN, $this->isString())
            ->willReturn(false);
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('isLocked')
            ->with(self::SECRET_CHAIN)
            ->willReturn(true);
        $refreshTokenRedisCacheFacadeMock->expects($this->never())->method('releaseLock');

        $refreshTokenRotationFacade = $this->createRefreshTokenRotationFacade($refreshTokenRedisCacheFacadeMock);
        $tokensData = $refreshTokenRotationFacade->getTokensDataOrGenerateForTest(
            $this->createStub(CustomerUser::class),
            self::SECRET_CHAIN,
            'device-id',
        );

        $this->assertSame($cachedTokensData->accessToken, $tokensData->accessToken);
        $this->assertSame($cachedTokensData->refreshToken, $tokensData->refreshToken);
    }

    public function testRefreshTokenWithoutCachedTokensIsRejectedWhenNoRequestHoldsLock(): void
    {
        $refreshTokenRedisCacheFacadeMock = $this->createMock(RefreshTokenRedisCacheFacade::class);
        $refreshTokenRedisCacheFacadeMock->expects($this->exactly(2))
            ->method('findCachedTokensData')
            ->with(self::SECRET_CHAIN)
            ->willReturn(null);
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('acquireLock')
            ->with(self::SECRET_CHAIN, $this->isString())
            ->willReturn(false);
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('isLocked')
            ->with(self::SECRET_CHAIN)
            ->willReturn(false);
        $refreshTokenRedisCacheFacadeMock->expects($this->never())->method('releaseLock');

        $refreshTokenRotationFacade = $this->createRefreshTokenRotationFacade($refreshTokenRedisCacheFacadeMock);

        $this->expectException(InvalidTokenUserMessageException::class);

        $refreshTokenRotationFacade->getTokensDataOrGenerateForTest(
            $this->createStub(CustomerUser::class),
            self::SECRET_CHAIN,
            'device-id',
        );
    }

    public function testRefreshTokenWithoutCachedTokensAndWithoutValidChainIsRejected(): void
    {
        $refreshTokenRedisCacheFacadeMock = $this->createMock(RefreshTokenRedisCacheFacade::class);
        $refreshTokenRedisCacheFacadeMock->expects($this->exactly(2))
            ->method('findCachedTokensData')
            ->with(self::SECRET_CHAIN)
            ->willReturn(null);
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('acquireLock')
            ->with(self::SECRET_CHAIN, $this->isString())
            ->willReturn(true);
        $refreshTokenRedisCacheFacadeMock->expects($this->never())->method('saveCachedTokensData');
        $refreshTokenRedisCacheFacadeMock->expects($this->once())
            ->method('releaseLock')
            ->with(self::SECRET_CHAIN, $this->isString());

        $refreshTokenRotationFacade = $this->createRefreshTokenRotationFacade(
            $refreshTokenRedisCacheFacadeMock,
            static fn (): TokensData => throw new InvalidTokenUserMessageException(),
        );

        $this->expectException(InvalidTokenUserMessageException::class);

        $refreshTokenRotationFacade->getTokensDataOrGenerateForTest(
            $this->createStub(CustomerUser::class),
            self::SECRET_CHAIN,
            'device-id',
        );
    }

    /**
     * @param (callable(): \Shopsys\FrontendApiBundle\Model\Security\TokensData)|null $generateTokensData
     */
    private function createRefreshTokenRotationFacade(
        RefreshTokenRedisCacheFacade $refreshTokenRedisCacheFacade,
        ?callable $generateTokensData = null,
    ): TestRefreshTokenRotationFacade {
        return new TestRefreshTokenRotationFacade(
            $this->createStub(TokenFacade::class),
            $this->createStub(CustomerUserFacade::class),
            $this->createStub(CustomerUserRefreshTokenChainFacade::class),
            new TokensDataFactory(),
            $refreshTokenRedisCacheFacade,
            $generateTokensData,
        );
    }

    private function createTokensData(string $accessToken, string $refreshToken): TokensData
    {
        return (new TokensDataFactory())->create($accessToken, $refreshToken);
    }
}
