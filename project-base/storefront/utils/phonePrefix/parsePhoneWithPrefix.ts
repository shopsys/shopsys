import { PhonePrefixesType } from 'utils/phonePrefix/usePhonePrefixes';

type ParseResult = { matched: true; countryCode: string; dialCode: string; localNumber: string } | { matched: false };

const MIN_LOCAL_NUMBER_LENGTH = 7;

const normalize = (value: string): string => value.replace(/[\s\-()]/g, '');

/**
 * Tries to detect an international dial code at the beginning of a phone number
 * and split it into (dialCode, localNumber).
 *
 * Handles three input formats:
 *   +420608913202   (explicit international prefix)
 *   00420608913202  (double-zero international prefix)
 *   420608913202    (raw digits, e.g. from password-manager autofill)
 *
 * Uses longest-match so "+421" (Slovakia) wins over "+42" (non-existent).
 * Requires the remaining local part to have at least MIN_LOCAL_NUMBER_LENGTH
 * digits to avoid false positives on short/local numbers.
 */
export const parsePhoneWithPrefix = (
    rawValue: string,
    availablePrefixes: PhonePrefixesType,
    currentCountryCode?: string,
): ParseResult => {
    const cleaned = normalize(rawValue);

    if (cleaned.length === 0) {
        return { matched: false };
    }

    let digits: string;

    if (cleaned.startsWith('+')) {
        digits = cleaned.slice(1);
    } else if (cleaned.startsWith('00') && cleaned.length > 2) {
        digits = cleaned.slice(2);
    } else {
        digits = cleaned;
    }

    if (!/^\d+$/.test(digits)) {
        return { matched: false };
    }

    // Build sorted dial-code entries (longest first for greedy matching).
    const entries = availablePrefixes
        .map((p) => ({ code: p.code, dialDigits: p.dialCode.replace(/\D/g, '') }))
        .sort((a, b) => b.dialDigits.length - a.dialDigits.length);

    for (const entry of entries) {
        if (!digits.startsWith(entry.dialDigits)) {
            continue;
        }

        const localNumber = digits.slice(entry.dialDigits.length);

        if (localNumber.length < MIN_LOCAL_NUMBER_LENGTH) {
            continue;
        }

        // Disambiguate when multiple countries share the same dial code.
        const allMatches = entries.filter((e) => e.dialDigits === entry.dialDigits);

        if (allMatches.length > 1 && currentCountryCode) {
            const preferred = allMatches.find((e) => e.code === currentCountryCode);

            if (preferred) {
                return {
                    matched: true,
                    countryCode: preferred.code,
                    dialCode: `+${preferred.dialDigits}`,
                    localNumber,
                };
            }
        }

        return {
            matched: true,
            countryCode: entry.code,
            dialCode: `+${entry.dialDigits}`,
            localNumber,
        };
    }

    return { matched: false };
};
