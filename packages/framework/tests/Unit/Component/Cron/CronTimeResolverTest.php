<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Cron;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeInterface;
use Shopsys\FrameworkBundle\Component\Cron\CronTimeResolver;
use Symfony\Component\Clock\DatePoint;

class CronTimeResolverTest extends TestCase
{
    public static function validCronExpressionProvider(): array
    {
        return [
            ['* * * * *'],
            ['0 3 * * *'],
            ['*/15 * * * *'],
            ['0 */2 * * *'],
            ['0,30 * * * *'],
            ['10 23 * * *'],
            ['0 4 * * 1'],
        ];
    }

    #[DataProvider('validCronExpressionProvider')]
    public function testValidateCronExpression(string $cronExpression): void
    {
        $cronTimeResolver = new CronTimeResolver();
        $cronTimeResolver->validateCronExpression($cronExpression);

        $this->expectNotToPerformAssertions();
    }

    public static function invalidCronExpressionProvider(): array
    {
        return [
            ['not a cron'],
            ['* * *'],
            ['60 * * * *'],
            ['* 25 * * *'],
        ];
    }

    #[DataProvider('invalidCronExpressionProvider')]
    public function testValidateCronExpressionThrowsOnInvalidExpression(string $cronExpression): void
    {
        $cronTimeResolver = new CronTimeResolver();
        $this->expectException(InvalidArgumentException::class);
        $cronTimeResolver->validateCronExpression($cronExpression);
    }

    public static function isValidAtTimeProvider(): array
    {
        return [
            ['0 0 * * *', '2000-01-01 00:00:00', true],
            ['* * * * *', '2000-01-01 12:12:00', true],
            ['* 1 * * *', '2000-01-01 01:12:00', true],
            ['2,3 * * * *', '2000-01-01 02:02:00', true],
            ['1,2 * * * *', '2000-01-01 00:02:00', true],
            ['*/15 * * * *', '2000-01-01 00:00:00', true],
            ['*/15 * * * *', '2000-01-01 00:15:00', true],
            ['*/15 * * * *', '2000-01-01 00:30:00', true],
            ['0 */4 * * *', '2000-01-01 08:00:00', true],
            ['* 2 * * *', '2000-01-01 02:00:00', true],
            ['0 * * * *', '2000-01-01 00:01:00', false],
            ['1,3 * * * *', '2000-01-01 00:02:00', false],
            ['*/10 * * * *', '2000-01-01 00:15:00', false],
            ['* */4 * * *', '2000-01-01 02:00:00', false],
        ];
    }

    #[DataProvider('isValidAtTimeProvider')]
    public function testIsValidAtTime(string $cronExpression, string $dateTimeString, bool $isValid): void
    {
        $cronTimeStub = $this->createStub(CronTimeInterface::class);
        $cronTimeStub->method('getCronExpression')->willReturn($cronExpression);

        $cronTimeResolver = new CronTimeResolver();

        $this->assertSame($isValid, $cronTimeResolver->isValidAtTime($cronTimeStub, new DatePoint($dateTimeString)));
    }
}
