<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Csv;

final class CsvHelper
{
    // mirrors the private CsvEncoder::FORMULAS_START_CHARACTERS used by the export to escape the values
    private const array FORMULA_TRIGGER_CHARACTERS = ['=', '-', '+', '@', "\t", "\r", "\n"];

    /**
     * Removes the apostrophe that the CSV export prepends to values that spreadsheet software would evaluate as formulas
     */
    public static function unescapeFormula(string $value): string
    {
        if (str_starts_with($value, "'") && in_array(substr($value, 1, 1), self::FORMULA_TRIGGER_CHARACTERS, true)) {
            return substr($value, 1);
        }

        return $value;
    }
}
