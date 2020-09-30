<?php

declare(strict_types=1);

namespace App\Component\Validator;

class RegexValidationRule
{
    public const MOEVE_DENY_CHARS = '/^[^őűàèìòùâêîôûâêîôãñõđåæøçłßþż€£¥ƒ¢§¶ªº]*$/u';

    public const STREET_ALPHABET_REGEX = '/[a-žA-Ž]+/';
    public const STREET_NUMBER_REGEX = '/[0-9]+/';

    public const COMPANY_NUMBER_REGEX = '/^[0-9]+$/';
    public const COMPANY_TAX_NUMBER_REGEX = '/^[0-9]+$/';
    public const COMPANY_NUMBER_WITH_VAT_REGEX = '/^[0-9A-Z]*([0-9]+[A-Z]+|[A-Z]+[0-9]+)[0-9A-Z]*$/';

    public const TELEPHONE_REGEX = '/^[0-9\+]+$/';
}
