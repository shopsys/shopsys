<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Model\Administrator\McpToken;

use DateTimeImmutable;
use InvalidArgumentException;
use Psr\Clock\ClockInterface;
use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class ManualTokenExpirationPresetEnum extends AbstractEnum
{
    public const string PRESET_1_DAY = '1_day';
    public const string PRESET_7_DAYS = '7_days';
    public const string PRESET_14_DAYS = '14_days';
    public const string PRESET_31_DAYS = '31_days';
    public const string PRESET_CUSTOM = 'custom';

    public function __construct(
        protected readonly ClockInterface $clock,
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getAllIndexedByTranslations(): array
    {
        return [
            t('1 day') => static::PRESET_1_DAY,
            t('7 days') => static::PRESET_7_DAYS,
            t('14 days') => static::PRESET_14_DAYS,
            t('31 days') => static::PRESET_31_DAYS,
            t('Custom') => static::PRESET_CUSTOM,
        ];
    }

    public function getExpiresAtByPreset(string $expirationPreset): DateTimeImmutable
    {
        $this->validateCase($expirationPreset);

        $dateTime = $this->clock->now();

        return match ($expirationPreset) {
            static::PRESET_1_DAY => $dateTime->modify('+1 day'),
            static::PRESET_7_DAYS => $dateTime->modify('+7 days'),
            static::PRESET_14_DAYS => $dateTime->modify('+14 days'),
            static::PRESET_31_DAYS => $dateTime->modify('+31 days'),
            static::PRESET_CUSTOM => throw new InvalidArgumentException('Custom expiration preset requires an explicit expiration date and time.'),
            default => throw new InvalidArgumentException('Invalid expiration preset.'),
        };
    }
}
