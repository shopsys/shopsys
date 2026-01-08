<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Command;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Redis;
use RedisException;
use Shopsys\FrameworkBundle\Command\CheckRedisCommand;
use Shopsys\FrameworkBundle\Component\Redis\RedisFacade;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

final class CheckRedisCommandTest extends TestCase
{
    /**
     * @return iterable
     */
    public static function pingAllRedisClientsProvider(): iterable
    {
        yield [true, []];

        yield [true, ['createRedisMockExpectingPing']];

        yield [true, ['createRedisMockExpectingPing', 'createRedisMockExpectingPing', 'createRedisMockExpectingPing']];

        yield [false, ['createRedisMockThrowingException']];

        yield [false, ['createRedisMockExpectingPing', 'createRedisMockThrowingException']];
    }

    /**
     * @param bool $expectSuccess
     * @param array $mockMethodNames
     */
    #[DataProvider('pingAllRedisClientsProvider')]
    public function testPingAllRedisClients(bool $expectSuccess, array $mockMethodNames): void
    {
        $redisMocks = [];

        foreach ($mockMethodNames as $mockMethodName) {
            $redisMocks[] = $this->{$mockMethodName}();
        }

        $redisFacade = new RedisFacade($redisMocks);
        $checkRedisCommand = new CheckRedisCommand($redisFacade);

        $output = new BufferedOutput();
        $returnCode = $checkRedisCommand->run(new StringInput(''), $output);

        $this->assertSame($expectSuccess ? 0 : 1, $returnCode);
        $this->assertStringContainsString(
            $expectSuccess ? 'Redis is available' : 'Redis is not available',
            $output->fetch(),
        );
    }

    /**
     * @return \Redis
     */
    private function createRedisMockExpectingPing(): Redis
    {
        /** @var \Redis|\PHPUnit\Framework\MockObject\MockObject $redisMock */
        $redisMock = $this->createMock(Redis::class);
        $redisMock->expects($this->once())->method('ping');

        return $redisMock;
    }

    /**
     * @return \Redis
     */
    private function createRedisMockThrowingException(): Redis
    {
        /** @var \Redis|\PHPUnit\Framework\MockObject\MockObject $redisMock */
        $redisMock = $this->createMock(Redis::class);
        $redisMock->method('ping')->willThrowException(new RedisException());

        return $redisMock;
    }
}
