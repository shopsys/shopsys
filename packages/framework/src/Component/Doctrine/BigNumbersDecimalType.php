<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Exception;
use Litipk\BigNumbers\Decimal;

class BigNumbersDecimalType extends Type
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'big_numbers_decimal';
    }

    /**
     * {@inheritDoc}
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getDecimalTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Decimal) {
            return (string)$value;
        }

        throw ConversionException::conversionFailedInvalidType($value, $this->getName(), ['null', Decimal::class]);
    }

    /**
     * {@inheritDoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Decimal
    {
        if ($value === null) {
            return null;
        }

        try {
            return Decimal::create($value);
        } catch (Exception $e) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), 'numeric', $e);
        }
    }

    /**
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
