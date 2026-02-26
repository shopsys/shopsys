<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Exception\InvalidFormat;
use Doctrine\DBAL\Types\Exception\InvalidType;
use Doctrine\DBAL\Types\Type;
use Exception;
use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;

class MoneyType extends Type
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getDecimalTypeDeclarationSQL($column);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Money) {
            return $value->getAmount();
        }

        throw InvalidType::new($value, static::class, ['null', Money::class]);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Money
    {
        if ($value === null) {
            return null;
        }

        try {
            return Money::create($value);
        } catch (Exception $e) {
            throw InvalidFormat::new($value, static::class, 'numeric', $e);
        }
    }
}
