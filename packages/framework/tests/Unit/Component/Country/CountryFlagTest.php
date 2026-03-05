<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Country;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Country\CountryFlag;

class CountryFlagTest extends TestCase
{
    public static function provideFlagsData(): iterable
    {
        yield ['US', '🇺🇸'];

        yield ['GB', '🇬🇧'];

        yield ['DE', '🇩🇪'];

        yield ['FR', '🇫🇷'];

        yield ['JP', '🇯🇵'];

        yield ['CN', '🇨🇳'];

        yield ['IN', '🇮🇳'];

        yield ['BR', '🇧🇷'];

        yield ['ZA', '🇿🇦'];

        yield ['CZ', '🇨🇿'];

        yield ['SK', '🇸🇰'];

        yield ['LONGER', ''];

        // Invalid country code
        yield ['FU', ''];
    }

    #[DataProvider('provideFlagsData')]
    public function testGetFlagEmoji(string $countryCode, string $expectedFlagEmoji): void
    {
        $this->assertSame($expectedFlagEmoji, CountryFlag::getFlagEmoji($countryCode));

        $countryCode = mb_strtolower($countryCode, 'UTF-8');
        $this->assertSame($expectedFlagEmoji, CountryFlag::getFlagEmoji($countryCode));
    }
}
