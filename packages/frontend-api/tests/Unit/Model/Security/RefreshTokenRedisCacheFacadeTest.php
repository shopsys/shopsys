<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Security;

use PHPUnit\Framework\TestCase;
use Redis;
use Shopsys\FrontendApiBundle\Model\Security\RefreshTokenRedisCacheFacade;
use Shopsys\FrontendApiBundle\Model\Security\TokensData;
use Shopsys\FrontendApiBundle\Model\Security\TokensDataFactory;

final class RefreshTokenRedisCacheFacadeTest extends TestCase
{
    private const string SECRET_CHAIN = 'secret-chain';

    public function testCachedTokensDataAreSavedWithCacheKeyAndTtl(): void
    {
        $tokensData = $this->createTokensData('access-token', 'refresh-token');
        /** @var \Redis|\PHPUnit\Framework\MockObject\MockObject $redisMock */
        $redisMock = $this->createMock(Redis::class);
        $redisMock->expects($this->once())
            ->method('setex')
            ->with($this->getCacheKey(), 10, $this->encodeTokensData($tokensData))
            ->willReturn(true);

        $refreshTokenRedisCacheFacade = $this->createRefreshTokenRedisCacheFacade($redisMock);
        $refreshTokenRedisCacheFacade->saveCachedTokensData(self::SECRET_CHAIN, $tokensData);
    }

    public function testCachedTokensDataAreLoadedByCacheKey(): void
    {
        $tokensData = $this->createTokensData('access-token', 'refresh-token');
        /** @var \Redis|\PHPUnit\Framework\MockObject\MockObject $redisMock */
        $redisMock = $this->createMock(Redis::class);
        $redisMock->expects($this->once())
            ->method('get')
            ->with($this->getCacheKey())
            ->willReturn($this->encodeTokensData($tokensData));

        $refreshTokenRedisCacheFacade = $this->createRefreshTokenRedisCacheFacade($redisMock);
        $cachedTokensData = $refreshTokenRedisCacheFacade->findCachedTokensData(self::SECRET_CHAIN);

        $this->assertNotNull($cachedTokensData);
        $this->assertSame($tokensData->accessToken, $cachedTokensData->accessToken);
        $this->assertSame($tokensData->refreshToken, $cachedTokensData->refreshToken);
    }

    public function testMissingCachedTokensDataReturnsNull(): void
    {
        /** @var \Redis|\PHPUnit\Framework\MockObject\MockObject $redisMock */
        $redisMock = $this->createMock(Redis::class);
        $redisMock->expects($this->once())
            ->method('get')
            ->with($this->getCacheKey())
            ->willReturn(false);

        $refreshTokenRedisCacheFacade = $this->createRefreshTokenRedisCacheFacade($redisMock);
        $cachedTokensData = $refreshTokenRedisCacheFacade->findCachedTokensData(self::SECRET_CHAIN);

        $this->assertNull($cachedTokensData);
    }

    public function testInvalidCachedTokensDataReturnsNull(): void
    {
        /** @var \Redis|\PHPUnit\Framework\MockObject\MockObject $redisMock */
        $redisMock = $this->createMock(Redis::class);
        $redisMock->expects($this->once())
            ->method('get')
            ->with($this->getCacheKey())
            ->willReturn('invalid-json');

        $refreshTokenRedisCacheFacade = $this->createRefreshTokenRedisCacheFacade($redisMock);
        $cachedTokensData = $refreshTokenRedisCacheFacade->findCachedTokensData(self::SECRET_CHAIN);

        $this->assertNull($cachedTokensData);
    }

    public function testLockIsAcquiredWithLockKeyAndTtl(): void
    {
        /** @var \Redis|\PHPUnit\Framework\MockObject\MockObject $redisMock */
        $redisMock = $this->createMock(Redis::class);
        $redisMock->expects($this->once())
            ->method('set')
            ->with($this->getLockKey(), 'lock-value', ['nx', 'ex' => 10])
            ->willReturn(true);

        $refreshTokenRedisCacheFacade = $this->createRefreshTokenRedisCacheFacade($redisMock);
        $lockAcquired = $refreshTokenRedisCacheFacade->acquireLock(self::SECRET_CHAIN, 'lock-value');

        $this->assertTrue($lockAcquired);
    }

    public function testLockIsReleasedOnlyForOwnerByLockKey(): void
    {
        /** @var \Redis|\PHPUnit\Framework\MockObject\MockObject $redisMock */
        $redisMock = $this->createMock(Redis::class);
        $redisMock->expects($this->once())
            ->method('eval')
            ->with(
                "if redis.call('get', KEYS[1]) == ARGV[1] then return redis.call('del', KEYS[1]) else return 0 end",
                [$this->getLockKey(), 'lock-value'],
                1,
            )
            ->willReturn(1);

        $refreshTokenRedisCacheFacade = $this->createRefreshTokenRedisCacheFacade($redisMock);
        $refreshTokenRedisCacheFacade->releaseLock(self::SECRET_CHAIN, 'lock-value');
    }

    private function createRefreshTokenRedisCacheFacade(Redis $redis): RefreshTokenRedisCacheFacade
    {
        return new RefreshTokenRedisCacheFacade($redis, new TokensDataFactory());
    }

    private function createTokensData(string $accessToken, string $refreshToken): TokensData
    {
        return (new TokensDataFactory())->create($accessToken, $refreshToken);
    }

    private function encodeTokensData(TokensData $tokensData): string
    {
        return json_encode([
            'accessToken' => $tokensData->accessToken,
            'refreshToken' => $tokensData->refreshToken,
        ], JSON_THROW_ON_ERROR);
    }

    private function getCacheKey(): string
    {
        return 'cache:' . hash('sha256', self::SECRET_CHAIN);
    }

    private function getLockKey(): string
    {
        return 'lock:' . hash('sha256', self::SECRET_CHAIN);
    }
}
