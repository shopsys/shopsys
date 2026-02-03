<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
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
    public function getName(): string
    {
        return 'money';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getDecimalTypeDeclarationSQL($fieldDeclaration);
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

        throw ConversionException::conversionFailedInvalidType($value, 'money', ['null', Money::class]);
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
            throw ConversionException::conversionFailedFormat($value, 'money', 'numeric', $e);
        }
    }

    #[Override]
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
