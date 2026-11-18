<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression;

use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use DateTimeInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Stringable;
use Transliterator;

/**
 * Evaluates the operations of the vocabulary on values held in memory:
 *
 * - text is searched regardless of case and diacritics — "hrnicek" finds "Hrníček";
 * - a number is compared with a number or with a number stored as text by its value — "100" equals 100;
 * - money is compared as a number, by its amount;
 * - two texts are compared as texts, even when they look like numbers — "0123" is not "123";
 * - two dates are compared chronologically;
 * - a boolean equals only the same boolean;
 * - any other pair of values cannot be compared and the operation fails.
 *
 * Every value of a record given here is present — a missing value (null) is decided by the caller.
 */
final class PhpValueComparator
{
    /**
     * Lower case without the diacritics of the Latin and the Greek script and of the Cyrillic Ё; no letter
     * is transliterated into the Latin script — "Hrníček" → "hrnicek", "Жёлтый" → "желтый".
     */
    private const string NORMALIZATION_RULES = ':: [[:Greek:]] NFD; :: [:Mn:] Remove; :: NFC; ё > е; Ё > Е; :: Latin-ASCII; :: Lower;';

    private ?Transliterator $transliterator = null;

    /**
     * @param string $operator The operation the values are compared for, named when they cannot be compared
     */
    public function equals(mixed $fieldValue, mixed $value, string $operator): bool
    {
        if (is_bool($fieldValue) && is_bool($value)) {
            return $fieldValue === $value;
        }

        return $this->compare($fieldValue, $value, $operator) === 0;
    }

    /**
     * @param string $operator The operation the values are compared for, named when they cannot be compared
     * @return int Negative, zero or positive as the value of the record is less than, equal to or greater than the given value
     */
    public function compare(mixed $fieldValue, mixed $value, string $operator): int
    {
        if ($fieldValue instanceof DateTimeInterface && $value instanceof DateTimeInterface) {
            return $fieldValue <=> $value;
        }

        if ($this->isNumber($fieldValue) || $this->isNumber($value)) {
            return $this->toDecimal($fieldValue, $operator)->compareTo($this->toDecimal($value, $operator));
        }

        // not by <=>, which compares two numeric texts as numbers
        if (is_string($fieldValue) && is_string($value)) {
            return strcmp($fieldValue, $value) <=> 0;
        }

        throw UnsupportedConditionException::operatorNotApplicableToValue($operator, $fieldValue);
    }

    /**
     * @param string $normalizedText The searched text, already normalized by `normalize()`
     */
    public function contains(mixed $fieldValue, string $normalizedText): bool
    {
        return str_contains($this->toComparableText($fieldValue, ExpressionOperatorEnum::CONTAINS), $normalizedText);
    }

    /**
     * @param string $normalizedText The searched text, already normalized by `normalize()`
     */
    public function startsWith(mixed $fieldValue, string $normalizedText): bool
    {
        return str_starts_with($this->toComparableText($fieldValue, ExpressionOperatorEnum::STARTS_WITH), $normalizedText);
    }

    /**
     * @param string $normalizedText The searched text, already normalized by `normalize()`
     */
    public function endsWith(mixed $fieldValue, string $normalizedText): bool
    {
        return str_ends_with($this->toComparableText($fieldValue, ExpressionOperatorEnum::ENDS_WITH), $normalizedText);
    }

    /**
     * Text in the form the text operations match it — "Hrníček Modrý" → "hrnicek modry".
     */
    public function normalize(string $text): string
    {
        $this->transliterator ??= Transliterator::createFromRules(self::NORMALIZATION_RULES);

        return $this->transliterator->transliterate($text);
    }

    /**
     * Text is searched in text only, a date or a number fails instead of matching nothing.
     */
    private function toComparableText(mixed $fieldValue, string $operator): string
    {
        if (is_string($fieldValue) || $fieldValue instanceof Stringable) {
            return $this->normalize((string)$fieldValue);
        }

        throw UnsupportedConditionException::operatorNotApplicableToValue($operator, $fieldValue);
    }

    private function isNumber(mixed $value): bool
    {
        return is_int($value) || is_float($value) || $value instanceof Money;
    }

    /**
     * A decimal keeps the full precision — as floats, "1.000000000000000001" and 1 would be equal.
     */
    private function toDecimal(mixed $value, string $operator): BigDecimal
    {
        try {
            return match (true) {
                $value instanceof Money => BigDecimal::of($value->getAmount()),
                is_int($value), is_float($value), is_string($value) => BigDecimal::of((string)$value),
                default => throw UnsupportedConditionException::operatorNotApplicableToValue($operator, $value),
            };
        } catch (MathException) {
            // a text that is no number, or one PHP reads as a number but a decimal does not (" 1", an infinite float)
            throw UnsupportedConditionException::operatorNotApplicableToValue($operator, $value);
        }
    }
}
