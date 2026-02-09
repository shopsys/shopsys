<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

class SpaydHelper
{
    /**
     * @param string $internationalBankAccountNumber Recipient account IBAN (e.g., CZ5508000000001535784133)
     * @param float|null $amount Amount (e.g., 1250.50), can be null
     * @param string $currency Currency, e.g., CZK or EUR
     * @param string|null $bankIdentifierCode BIC/SWIFT (required for international EUR payment)
     * @param string|null $variableSymbol Variable symbol
     * @param string|null $specificSymbol Specific symbol
     * @param string|null $constantSymbol Constant symbol
     * @param string|null $message Message for the recipient
     */
    public static function createSpayd(
        string $internationalBankAccountNumber,
        ?float $amount = null,
        string $currency = 'CZK',
        ?string $bankIdentifierCode = null,
        ?string $variableSymbol = null,
        ?string $specificSymbol = null,
        ?string $constantSymbol = null,
        ?string $message = null,
    ): string {
        $parts = [];
        $parts[] = 'SPD*1.0';
        $parts[] = 'ACC:' . $internationalBankAccountNumber;

        if ($amount !== null) {
            $parts[] = 'AM:' . number_format($amount, 2, '.', '');
        }

        $parts[] = 'CC:' . strtoupper($currency);

        if (strtoupper($currency) === 'EUR' && $bankIdentifierCode !== null) {
            $parts[] = 'BIC:' . strtoupper($bankIdentifierCode);
        }

        if ($variableSymbol !== null) {
            $parts[] = 'X-VS:' . $variableSymbol;
        }

        if ($specificSymbol !== null) {
            $parts[] = 'X-SS:' . $specificSymbol;
        }

        if ($constantSymbol !== null) {
            $parts[] = 'X-KS:' . $constantSymbol;
        }

        if ($message !== null) {
            $message = str_replace(' ', '+', $message);
            $parts[] = 'MSG:' . $message;
        }

        return implode('*', $parts);
    }
}
