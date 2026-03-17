import { applyStorefrontDevelopmentCspAppendices } from 'utils/serverSide/cspHelpers';
import { describe, expect, test } from 'vitest';

describe('applyStorefrontDevelopmentCspAppendices', () => {
    test("appends 'unsafe-eval' to script-src directive", () => {
        const csp = "default-src 'self'; script-src 'self'; style-src 'self'";
        const result = applyStorefrontDevelopmentCspAppendices(csp);
        expect(result).toBe("default-src 'self'; script-src 'self' 'unsafe-eval'; style-src 'self'");
    });

    test("does not duplicate 'unsafe-eval' if already present", () => {
        const csp = "default-src 'self'; script-src 'self' 'unsafe-eval'; style-src 'self'";
        const result = applyStorefrontDevelopmentCspAppendices(csp);
        expect(result).toBe("default-src 'self'; script-src 'self' 'unsafe-eval'; style-src 'self'");
    });

    test('returns original header when script-src directive is missing', () => {
        const csp = "default-src 'self'; style-src 'self'";
        const result = applyStorefrontDevelopmentCspAppendices(csp);
        expect(result).toBe(csp);
    });

    test('handles empty CSP header', () => {
        const result = applyStorefrontDevelopmentCspAppendices('');
        expect(result).toBe('');
    });

    test('handles script-src as the only directive', () => {
        const csp = "script-src 'self'";
        const result = applyStorefrontDevelopmentCspAppendices(csp);
        expect(result).toBe("script-src 'self' 'unsafe-eval'");
    });

    test('handles script-src with multiple existing sources', () => {
        const csp = "script-src 'self' https://cdn.example.com 'nonce-abc123'";
        const result = applyStorefrontDevelopmentCspAppendices(csp);
        expect(result).toBe("script-src 'self' https://cdn.example.com 'nonce-abc123' 'unsafe-eval'");
    });
});
