import { PublicRuntimeConfig, serializeConfigForScriptTag } from 'envConfig';
import { describe, expect, it } from 'vitest';
import { defaultTestConfig } from 'vitest/helpers/mockPublicConfig';

function makeConfig(overrides: Partial<PublicRuntimeConfig> = {}): PublicRuntimeConfig {
    return { ...defaultTestConfig, ...overrides };
}

function assertNoScriptClose(serialized: string): void {
    expect(serialized.toLowerCase()).not.toContain('</script');
}

function assertValidJsAssignment(serialized: string): void {
    // Must parse as valid JS when embedded in window.__ENV=...;
    const js = `var __ENV=${serialized};`;
    // Intentionally using the Function constructor to verify the serialized payload stays valid JS.
    expect(() => new Function(js)).not.toThrow();
}

function assertRoundtrip(serialized: string, original: PublicRuntimeConfig): void {
    const parsed = JSON.parse(serialized) as PublicRuntimeConfig;
    expect(parsed).toEqual(original);
}

describe('serializeConfigForScriptTag — XSS & Serialization Security', () => {
    describe('</script> injection vectors', () => {
        it('escapes </script> in a string value', () => {
            const config = makeConfig({ cdnDomain: '</script><script>alert(1)</script>' });
            const result = serializeConfigForScriptTag(config);

            assertNoScriptClose(result);
            assertValidJsAssignment(result);
            assertRoundtrip(result, config);
        });

        it('escapes </script> in nested domain URL', () => {
            const config = makeConfig({
                domains: [
                    {
                        ...defaultTestConfig.domains[0],
                        url: 'https://example.com/</script><script>alert(1)</script>',
                    },
                ],
            });
            const result = serializeConfigForScriptTag(config);

            assertNoScriptClose(result);
            assertRoundtrip(result, config);
        });

        it('escapes </script> in GTM ID', () => {
            const config = makeConfig({
                domains: [
                    {
                        ...defaultTestConfig.domains[0],
                        gtmId: '</script><img src=x onerror=alert(1)>',
                    },
                ],
            });
            const result = serializeConfigForScriptTag(config);

            assertNoScriptClose(result);
            assertRoundtrip(result, config);
        });

        it('escapes </script> in all string fields simultaneously', () => {
            const payload = '</script><script>alert(1)</script>';
            const config = makeConfig({
                cdnDomain: payload,
                sentryDsn: payload,
                sentryEnvironment: payload,
                errorDebuggingLevel: payload,
                showSymfonyToolbar: payload,
                userSnapApiKey: payload,
                googleMapApiKey: payload,
                packeteryApiKey: payload,
            });
            const result = serializeConfigForScriptTag(config);

            assertNoScriptClose(result);
            assertRoundtrip(result, config);
        });
    });

    describe('case-insensitive script tag variations', () => {
        it('escapes </SCRIPT> (uppercase)', () => {
            const config = makeConfig({ cdnDomain: '</SCRIPT>' });
            const result = serializeConfigForScriptTag(config);

            assertNoScriptClose(result);
            assertRoundtrip(result, config);
        });

        it('escapes </ScRiPt> (mixed case)', () => {
            const config = makeConfig({ cdnDomain: '</ScRiPt>' });
            const result = serializeConfigForScriptTag(config);

            assertNoScriptClose(result);
            assertRoundtrip(result, config);
        });
    });

    describe('HTML comment injection', () => {
        it('escapes <!-- in values', () => {
            const config = makeConfig({ cdnDomain: '<!--' });
            const result = serializeConfigForScriptTag(config);

            expect(result).not.toContain('<!--');
            assertRoundtrip(result, config);
        });

        it('escapes <!--> in values', () => {
            const config = makeConfig({ cdnDomain: '<!-->' });
            const result = serializeConfigForScriptTag(config);

            expect(result).not.toContain('<!-->');
            assertRoundtrip(result, config);
        });
    });

    describe('backslash interactions', () => {
        it('handles \\</script> (single backslash before <)', () => {
            const config = makeConfig({ cdnDomain: '\\</script>' });
            const result = serializeConfigForScriptTag(config);

            assertNoScriptClose(result);
            assertRoundtrip(result, config);
        });

        it('handles \\\\</script> (double backslash before <)', () => {
            const config = makeConfig({ cdnDomain: '\\\\</script>' });
            const result = serializeConfigForScriptTag(config);

            assertNoScriptClose(result);
            assertRoundtrip(result, config);
        });

        it('handles odd number of backslashes before <', () => {
            const config = makeConfig({ cdnDomain: '\\\\\\</script>' });
            const result = serializeConfigForScriptTag(config);

            assertNoScriptClose(result);
            assertRoundtrip(result, config);
        });
    });

    describe('Unicode bypass attempts', () => {
        it('handles literal \\u003c in env var (double-encoding check)', () => {
            const config = makeConfig({ cdnDomain: '\\u003c/script>' });
            const result = serializeConfigForScriptTag(config);

            assertNoScriptClose(result);
            const parsed = JSON.parse(result) as PublicRuntimeConfig;
            expect(parsed.cdnDomain).toBe('\\u003c/script>');
        });

        it('handles fullwidth < (U+FF1C)', () => {
            const config = makeConfig({ cdnDomain: '\uFF1C/script>' });
            const result = serializeConfigForScriptTag(config);

            assertRoundtrip(result, config);
        });

        it('handles math angle bracket (U+27E8)', () => {
            const config = makeConfig({ cdnDomain: '\u27E8/script>' });
            const result = serializeConfigForScriptTag(config);

            assertRoundtrip(result, config);
        });
    });

    describe('JSON edge cases', () => {
        it('handles null bytes', () => {
            const config = makeConfig({ cdnDomain: 'before\0after' });
            const result = serializeConfigForScriptTag(config);

            assertValidJsAssignment(result);
            assertRoundtrip(result, config);
        });

        it('handles control characters', () => {
            const config = makeConfig({ cdnDomain: '\t\n\r\b\f' });
            const result = serializeConfigForScriptTag(config);

            assertValidJsAssignment(result);
            assertRoundtrip(result, config);
        });

        it('handles embedded quotes', () => {
            const config = makeConfig({ cdnDomain: 'say "hello" and \'goodbye\'' });
            const result = serializeConfigForScriptTag(config);

            assertValidJsAssignment(result);
            assertRoundtrip(result, config);
        });

        it('handles JSON structural characters', () => {
            const config = makeConfig({ cdnDomain: '{}[],:' });
            const result = serializeConfigForScriptTag(config);

            assertValidJsAssignment(result);
            assertRoundtrip(result, config);
        });

        it('handles very long strings (100k characters)', () => {
            const config = makeConfig({ cdnDomain: 'x'.repeat(100_000) });
            const result = serializeConfigForScriptTag(config);

            assertValidJsAssignment(result);
            assertRoundtrip(result, config);
        });

        it('handles emoji and multi-byte Unicode', () => {
            const config = makeConfig({ cdnDomain: '🎉🔥💻 Ñoño café résumé' });
            const result = serializeConfigForScriptTag(config);

            assertValidJsAssignment(result);
            assertRoundtrip(result, config);
        });
    });

    describe('roundtrip integrity', () => {
        it('preserves full default config through serialize/parse roundtrip', () => {
            const config = makeConfig();
            const result = serializeConfigForScriptTag(config);

            assertRoundtrip(result, config);
        });

        it('preserves config with XSS payloads in every string field', () => {
            const payload = '</script><img onerror=alert(1) src=x>';
            const config = makeConfig({
                cdnDomain: payload,
                sentryDsn: payload,
                sentryEnvironment: payload,
                errorDebuggingLevel: payload,
                showSymfonyToolbar: payload,
                userSnapApiKey: payload,
                googleMapApiKey: payload,
                packeteryApiKey: payload,
                domains: [
                    {
                        ...defaultTestConfig.domains[0],
                        url: payload,
                        publicGraphqlEndpoint: payload,
                        gtmId: payload,
                    },
                ],
            });
            const result = serializeConfigForScriptTag(config);

            assertNoScriptClose(result);
            assertRoundtrip(result, config);
        });
    });

    describe('valid JS expression', () => {
        it('output embedded in window.__ENV=...; does not contain </script>', () => {
            const payload = '</script><script>alert(document.cookie)</script>';
            const config = makeConfig({ cdnDomain: payload, sentryDsn: payload });
            const result = serializeConfigForScriptTag(config);
            const fullScript = `window.__ENV=${result};`;

            expect(fullScript.toLowerCase()).not.toContain('</script');
            // Intentionally using the Function constructor to verify the embedded script stays valid JS.
            expect(() => new Function(fullScript)).not.toThrow();
        });
    });
});
