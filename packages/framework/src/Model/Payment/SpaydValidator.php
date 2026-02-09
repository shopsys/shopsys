<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use InvalidArgumentException;

class SpaydValidator
{
    public function validate(string $spayd): void
    {
        $spayd = trim($spayd);

        if ($spayd === '') {
            throw new InvalidArgumentException('SPAYD string is empty.');
        }

        $parts = explode('*', $spayd);

        if (count($parts) < 3) {
            throw new InvalidArgumentException('SPAYD string is incomplete.');
        }

        if ($parts[0] !== 'SPD') {
            throw new InvalidArgumentException('SPAYD must start with "SPD".');
        }

        $version = $parts[1];

        if (!in_array($version, ['1.0', '1.1'], true)) {
            throw new InvalidArgumentException(sprintf('Unsupported SPAYD version "%s".', $version));
        }

        $fields = $this->parseFields(array_slice($parts, 2));

        foreach (['ACC', 'AM', 'CC'] as $requiredKey) {
            if (!array_key_exists($requiredKey, $fields)) {
                throw new InvalidArgumentException(sprintf('Missing required SPAYD field: %s.', $requiredKey));
            }
        }

        // ACC: IBAN [+BIC] – validate IBAN (general) and optional BIC
        $acc = $fields['ACC'];
        // Either IBAN or IBAN+BIC separated by '+'
        $ibanPart = $acc;

        if (str_contains($acc, '+')) {
            [$ibanPart, $bic] = explode('+', $acc, 2);
            $this->assertValidBic($bic);
        }
        $ibanPart = strtoupper($ibanPart);
        $this->assertValidIban($ibanPart);

        // AM: positive amount, up to 2 decimal places
        $amountRaw = $fields['AM'];
        $amountNormalized = str_replace(',', '.', $amountRaw);

        if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $amountNormalized)) {
            throw new InvalidArgumentException('Invalid AM (amount) format. Expect digits with optional dot and up to 2 decimals.');
        }
        $amount = (float)$amountNormalized;

        if (!is_finite($amount) || $amount <= 0.0) {
            throw new InvalidArgumentException('AM (amount) must be a positive number.');
        }

        // CC: 3-letter ISO currency code
        $cc = $fields['CC'];

        if (!preg_match('/^[A-Z]{3}$/', $cc)) {
            throw new InvalidArgumentException('CC (currency) must be a 3-letter uppercase ISO code.');
        }

        // X-VS: optional, 1–10 digits per CZ/SK standard
        if (isset($fields['X-VS']) && !preg_match('/^\d{1,10}$/', $fields['X-VS'])) {
            throw new InvalidArgumentException('X-VS (variable symbol) must be 1 to 10 digits.');
        }
    }

    /**
     * @param array<int,string> $rawFields
     * @return array<string,string>
     */
    protected function parseFields(array $rawFields): array
    {
        $fields = [];

        foreach ($rawFields as $segment) {
            if ($segment === '') {
                // ignore empty segment (possible double *)
                continue;
            }

            $pos = strpos($segment, ':');

            if ($pos === false) {
                throw new InvalidArgumentException(sprintf('Invalid field segment (missing ":"): "%s".', $segment));
            }

            $key = substr($segment, 0, $pos);
            $value = substr($segment, $pos + 1);

            if ($key === '' || !preg_match('/^[A-Z][A-Z0-9-]*$/', $key)) {
                throw new InvalidArgumentException(sprintf('Invalid field key: "%s".', $key));
            }

            if (array_key_exists($key, $fields)) {
                throw new InvalidArgumentException(sprintf('Duplicate field key: "%s".', $key));
            }

            // Keep the value as is (SPAYD allows certain characters/escaping),
            // since our generator does not include special escaping.
            $fields[$key] = $value;
        }

        return $fields;
    }

    /**
     * Validates BIC (8 or 11 characters, A–Z and 0–9), basic syntactic check.
     */
    protected function assertValidBic(string $bic): void
    {
        $bic = strtoupper($bic);

        if (!preg_match('/^[A-Z0-9]{8}([A-Z0-9]{3})?$/', $bic)) {
            throw new InvalidArgumentException('Invalid BIC format in ACC field.');
        }
    }

    /**
     * Validates IBAN in general (ISO 13616) and its checksum (mod 97 == 1).
     * Any country code (A–Z) allowed, length 15–34, format: CCkk... (2-letter country code + 2 check digits).
     */
    protected function assertValidIban(string $iban): void
    {
        $iban = strtoupper($iban);

        // CC (A–Z), 2 check digits (0–9), the rest alphanumeric
        if (!preg_match('/^[A-Z]{2}\d{2}[0-9A-Z]{11,30}$/', $iban)) {
            throw new InvalidArgumentException('ACC must contain a valid IBAN (two letters country code, two check digits, followed by alphanumerics).');
        }

        $length = strlen($iban);

        if ($length < 15 || $length > 34) {
            throw new InvalidArgumentException(sprintf('Invalid IBAN length (expected 15 to 34, got %d).', $length));
        }

        if (!$this->ibanChecksumIsValid($iban)) {
            throw new InvalidArgumentException('Invalid IBAN checksum in ACC field.');
        }
    }

    /**
     * IBAN checksum (ISO 13616): move the first four characters to the end,
     * convert letters to numbers (A=10 ... Z=35), the number mod 97 must be 1.
     */
    protected function ibanChecksumIsValid(string $iban): bool
    {
        $iban = strtoupper($iban);
        $rearranged = substr($iban, 4) . substr($iban, 0, 4);

        $numeric = '';
        $len = strlen($rearranged);

        for ($i = 0; $i < $len; $i++) {
            $ch = $rearranged[$i];

            if ($ch >= 'A' && $ch <= 'Z') {
                $numeric .= (ord($ch) - 55); // 'A'->10 ... 'Z'->35
            } else {
                $numeric .= $ch;
            }
        }

        return bcmod($numeric, '97') === '1';
    }
}
