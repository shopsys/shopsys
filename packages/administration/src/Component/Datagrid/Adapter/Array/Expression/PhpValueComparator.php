<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression;

use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use DateTimeInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotApplicableToValueException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Stringable;
use Transliterator;

/**
 * Compares the value of a record with the value of an expression the way a database does, so that the
 * in-memory medium answers the same as a query — a number stored as a string is still a number, money is
 * compared by its amount, and text is matched regardless of case and diacritics.
 */
final class PhpValueComparator
{
    /**
     * The in-memory counterpart of the `NORMALIZED()` function of the database (lower case, no diacritics),
     * so that both media match the same text.
     */
    private const string NORMALIZATION_RULES = 'Any-Latin; Latin-ASCII; Lower';

    private ?Transliterator $transliterator = null;

    public function equals(mixed $fieldValue, mixed $value): bool
    {
        $comparison = $this->compare($fieldValue, $value);

        if ($comparison !== null) {
            return $comparison === 0;
        }

        return $fieldValue === $value;
    }

    /**
     * @return int|null Null when the values cannot be ordered, in which case nothing matches
     */
    public function compare(mixed $fieldValue, mixed $value): ?int
    {
        if ($fieldValue === null || $value === null) {
            return null;
        }

        if ($fieldValue instanceof Money || $value instanceof Money) {
            return $this->compareNumbers($this->toNumber($fieldValue), $this->toNumber($value));
        }

        if ($fieldValue instanceof DateTimeInterface && $value instanceof DateTimeInterface) {
            return $fieldValue <=> $value;
        }

        if (is_numeric($fieldValue) && is_numeric($value)) {
            return $this->compareNumbers($this->toNumber($fieldValue), $this->toNumber($value));
        }

        if (is_string($fieldValue) && is_string($value)) {
            return $fieldValue <=> $value;
        }

        return null;
    }

    public function contains(mixed $fieldValue, string $value): bool
    {
        $text = $this->toComparableText($fieldValue, ExpressionOperatorEnum::CONTAINS);

        return $text !== null && str_contains($text, $this->normalize($value));
    }

    public function startsWith(mixed $fieldValue, string $value): bool
    {
        $text = $this->toComparableText($fieldValue, ExpressionOperatorEnum::STARTS_WITH);

        return $text !== null && str_starts_with($text, $this->normalize($value));
    }

    public function endsWith(mixed $fieldValue, string $value): bool
    {
        $text = $this->toComparableText($fieldValue, ExpressionOperatorEnum::ENDS_WITH);

        return $text !== null && str_ends_with($text, $this->normalize($value));
    }

    /**
     * Text is searched in text only — a database refuses to search a date or a number, so does this medium,
     * instead of quietly matching nothing.
     */
    private function toComparableText(mixed $fieldValue, string $operator): ?string
    {
        if ($fieldValue === null) {
            return null;
        }

        if (is_string($fieldValue) || $fieldValue instanceof Stringable) {
            return $this->normalize((string)$fieldValue);
        }

        throw new OperatorNotApplicableToValueException($operator, $fieldValue);
    }

    private function normalize(string $text): string
    {
        $this->transliterator ??= Transliterator::create(self::NORMALIZATION_RULES);

        return $this->transliterator->transliterate($text);
    }

    /**
     * A decimal is compared to its full precision, the way the database does it — casting it to a float
     * would make two different values of a high precision column equal.
     */
    private function compareNumbers(?BigDecimal $fieldNumber, ?BigDecimal $number): ?int
    {
        return $fieldNumber !== null && $number !== null ? $fieldNumber->compareTo($number) : null;
    }

    private function toNumber(mixed $value): ?BigDecimal
    {
        try {
            if ($value instanceof Money) {
                return BigDecimal::of($value->getAmount());
            }

            return is_numeric($value) ? BigDecimal::of((string)$value) : null;
        } catch (MathException) {
            return null;
        }
    }
}
