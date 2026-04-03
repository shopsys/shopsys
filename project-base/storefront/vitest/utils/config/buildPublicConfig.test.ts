import { buildPublicConfig } from 'buildPublicEnvConfig';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';

describe('buildPublicConfig', () => {
    const originalEnv = process.env;

    // Build a baseline config to discover how many domains are defined
    let domainCount: number;
    let baselineConfig: ReturnType<typeof buildPublicConfig>;

    beforeEach(() => {
        process.env = { ...originalEnv };

        // Set all DOMAIN_HOSTNAME_N env vars the config expects
        const tempEnv = { ...originalEnv };
        // We need to discover domain count - build with a generous set of hostnames
        for (let i = 1; i <= 10; i++) {
            tempEnv[`DOMAIN_HOSTNAME_${i}`] = `https://domain${i}.example.com/`;
        }
        process.env = tempEnv;
        baselineConfig = buildPublicConfig();
        domainCount = baselineConfig.domains.length;

        // Reset env with exactly the needed hostnames
        process.env = { ...originalEnv };
        for (let i = 1; i <= domainCount; i++) {
            process.env[`DOMAIN_HOSTNAME_${i}`] = `https://domain${i}.example.com/`;
        }
    });

    afterEach(() => {
        process.env = originalEnv;
    });

    describe('domain URL validation', () => {
        it('throws when DOMAIN_HOSTNAME_1 is missing', () => {
            delete process.env.DOMAIN_HOSTNAME_1;

            expect(() => buildPublicConfig()).toThrow('DOMAIN_HOSTNAME_1 is required but not set.');
        });

        it.skipIf(
            (() => {
                // Eagerly compute domain count for skip checks
                const env = { ...originalEnv };
                for (let i = 1; i <= 10; i++) {
                    env[`DOMAIN_HOSTNAME_${i}`] = `https://domain${i}.example.com/`;
                }
                const origProcessEnv = process.env;
                process.env = env;
                try {
                    const cfg = buildPublicConfig();
                    return cfg.domains.length < 2;
                } finally {
                    process.env = origProcessEnv;
                }
            })(),
        )('throws when DOMAIN_HOSTNAME_2 is missing', () => {
            delete process.env.DOMAIN_HOSTNAME_2;

            expect(() => buildPublicConfig()).toThrow('DOMAIN_HOSTNAME_2 is required but not set.');
        });

        it.skipIf(
            (() => {
                const env = { ...originalEnv };
                for (let i = 1; i <= 10; i++) {
                    env[`DOMAIN_HOSTNAME_${i}`] = `https://domain${i}.example.com/`;
                }
                const origProcessEnv = process.env;
                process.env = env;
                try {
                    const cfg = buildPublicConfig();
                    return cfg.domains.length < 3;
                } finally {
                    process.env = origProcessEnv;
                }
            })(),
        )('throws when DOMAIN_HOSTNAME_3 is missing', () => {
            delete process.env.DOMAIN_HOSTNAME_3;

            expect(() => buildPublicConfig()).toThrow('DOMAIN_HOSTNAME_3 is required but not set.');
        });

        it('throws when domain URL is not a valid URL', () => {
            process.env.DOMAIN_HOSTNAME_1 = 'not-a-url';

            expect(() => buildPublicConfig()).toThrow('DOMAIN_HOSTNAME_1="not-a-url" is not a valid URL.');
        });

        it('throws for URL without protocol on any domain', () => {
            process.env.DOMAIN_HOSTNAME_1 = 'example.com';

            expect(() => buildPublicConfig()).toThrow('DOMAIN_HOSTNAME_1="example.com" is not a valid URL.');
        });

        it('accepts valid URLs with different protocols', () => {
            process.env.DOMAIN_HOSTNAME_1 = 'https://example.com/';

            expect(() => buildPublicConfig()).not.toThrow();
        });
    });

    describe('boolean coercion safety', () => {
        const booleanEnvVars: Array<{ envVar: string; configKey: string }> = [
            { envVar: 'SENTRY_FEEDBACK_ENABLE', configKey: 'sentryFeedbackEnable' },
            { envVar: 'SENTRY_REPLAYS_ENABLE', configKey: 'sentryReplaysEnable' },
            { envVar: 'SHOULD_USE_DEFER', configKey: 'shouldUseDefer' },
            { envVar: 'USERSNAP_STOREFRONT_ENABLED_BY_DEFAULT', configKey: 'userSnapEnabledDefaultValue' },
        ];

        it.each(booleanEnvVars)('$configKey: exact "1" produces true', ({ envVar, configKey }) => {
            process.env[envVar] = '1';
            const config = buildPublicConfig();

            expect(config[configKey as keyof typeof config]).toBe(true);
        });

        it.each(booleanEnvVars)('$configKey: undefined produces false', ({ envVar, configKey }) => {
            delete process.env[envVar];
            const config = buildPublicConfig();

            expect(config[configKey as keyof typeof config]).toBe(false);
        });

        const falseValues = ['true', 'yes', '1 ', ' 1', '01', 'on', '', '0', 'false'];

        it.each(booleanEnvVars)('$configKey: rejects non-"1" values as false', ({ envVar, configKey }) => {
            for (const value of falseValues) {
                process.env[envVar] = value;
                const config = buildPublicConfig();

                expect(config[configKey as keyof typeof config]).toBe(false);
            }
        });
    });

    describe('string defaults', () => {
        it('all string fields default to empty string when env vars are missing', () => {
            delete process.env.GOOGLE_MAP_API_KEY;
            delete process.env.PACKETERY_API_KEY;
            delete process.env.CDN_DOMAIN;
            delete process.env.SENTRY_DSN;
            delete process.env.SENTRY_ENVIRONMENT;
            delete process.env.ERROR_DEBUGGING_LEVEL;
            delete process.env.SHOW_SYMFONY_TOOLBAR;
            delete process.env.USERSNAP_PROJECT_API_KEY;

            const config = buildPublicConfig();

            expect(config.googleMapApiKey).toBe('');
            expect(config.packeteryApiKey).toBe('');
            expect(config.cdnDomain).toBe('');
            expect(config.sentryDsn).toBe('');
            expect(config.sentryEnvironment).toBe('');
            expect(config.errorDebuggingLevel).toBe('');
            expect(config.showSymfonyToolbar).toBe('');
            expect(config.userSnapApiKey).toBe('');
        });
    });

    describe('sentry DSN trimming', () => {
        it('trims whitespace from SENTRY_DSN', () => {
            process.env.SENTRY_DSN = '  https://sentry.example.com/123  ';
            const config = buildPublicConfig();

            expect(config.sentryDsn).toBe('https://sentry.example.com/123');
        });

        it('trims tabs and newlines from SENTRY_DSN', () => {
            process.env.SENTRY_DSN = '\thttps://sentry.example.com/123\n';
            const config = buildPublicConfig();

            expect(config.sentryDsn).toBe('https://sentry.example.com/123');
        });

        it('produces empty string for whitespace-only SENTRY_DSN', () => {
            process.env.SENTRY_DSN = '   \t\n  ';
            const config = buildPublicConfig();

            expect(config.sentryDsn).toBe('');
        });
    });

    describe('LUIGIS_BOX_ENABLED_DOMAIN_IDS parsing', () => {
        it('activates all domains when all IDs are listed', () => {
            const allIds = Array.from({ length: domainCount }, (_, i) => String(i + 1)).join(',');
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = allIds;
            const config = buildPublicConfig();

            for (let i = 0; i < domainCount; i++) {
                expect(config.domains[i].isLuigisBoxActive).toBe(true);
            }
        });

        it('empty string disables all domains', () => {
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = '';
            const config = buildPublicConfig();

            for (let i = 0; i < domainCount; i++) {
                expect(config.domains[i].isLuigisBoxActive).toBe(false);
            }
        });

        it('trailing comma does not cause issues', () => {
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = '1,';
            const config = buildPublicConfig();

            expect(config.domains[0].isLuigisBoxActive).toBe(true);
        });

        it('spaces around numbers are NOT trimmed (exact match)', () => {
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = ' 1';
            const config = buildPublicConfig();

            // ' 1' !== '1', so domain 1 should NOT be active
            expect(config.domains[0].isLuigisBoxActive).toBe(false);
        });

        it('single ID "1" activates only domain 1', () => {
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = '1';
            const config = buildPublicConfig();

            expect(config.domains[0].isLuigisBoxActive).toBe(true);
            for (let i = 1; i < domainCount; i++) {
                expect(config.domains[i].isLuigisBoxActive).toBe(false);
            }
        });

        it('undefined env var disables all domains', () => {
            delete process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS;
            const config = buildPublicConfig();

            for (let i = 0; i < domainCount; i++) {
                expect(config.domains[i].isLuigisBoxActive).toBe(false);
            }
        });
    });

    describe('malicious env values', () => {
        it('handles JS code in string fields', () => {
            process.env.CDN_DOMAIN = 'alert(document.cookie)';
            const config = buildPublicConfig();

            expect(config.cdnDomain).toBe('alert(document.cookie)');
        });

        it('handles JSON structural characters', () => {
            process.env.CDN_DOMAIN = '{"key":"val"}';
            const config = buildPublicConfig();

            expect(config.cdnDomain).toBe('{"key":"val"}');
        });

        it('handles newlines in values', () => {
            process.env.CDN_DOMAIN = 'line1\nline2';
            const config = buildPublicConfig();

            expect(config.cdnDomain).toBe('line1\nline2');
        });

        it('handles "null" literal string', () => {
            process.env.CDN_DOMAIN = 'null';
            const config = buildPublicConfig();

            expect(config.cdnDomain).toBe('null');
        });

        it('handles "undefined" literal string', () => {
            process.env.CDN_DOMAIN = 'undefined';
            const config = buildPublicConfig();

            expect(config.cdnDomain).toBe('undefined');
        });

        it('handles "0" string (truthy for ?? operator)', () => {
            process.env.CDN_DOMAIN = '0';
            const config = buildPublicConfig();

            expect(config.cdnDomain).toBe('0');
        });
    });

    describe('per-domain GTM ID', () => {
        it('uses per-domain GTM_ID_N when set', () => {
            for (let i = 1; i <= domainCount; i++) {
                process.env[`GTM_ID_${i}`] = `GTM-DOMAIN${i}`;
            }
            const config = buildPublicConfig();

            for (let i = 0; i < domainCount; i++) {
                expect(config.domains[i].gtmId).toBe(`GTM-DOMAIN${i + 1}`);
            }
        });

        it('falls back to shared GTM_ID when per-domain vars are not set', () => {
            process.env.GTM_ID = 'GTM-SHARED';
            for (let i = 1; i <= domainCount; i++) {
                delete process.env[`GTM_ID_${i}`];
            }
            const config = buildPublicConfig();

            for (let i = 0; i < domainCount; i++) {
                expect(config.domains[i].gtmId).toBe('GTM-SHARED');
            }
        });

        it('per-domain GTM_ID_N takes priority over shared GTM_ID', () => {
            process.env.GTM_ID = 'GTM-SHARED';
            process.env.GTM_ID_1 = 'GTM-OVERRIDE';
            for (let i = 2; i <= domainCount; i++) {
                delete process.env[`GTM_ID_${i}`];
            }
            const config = buildPublicConfig();

            expect(config.domains[0].gtmId).toBe('GTM-OVERRIDE');
            for (let i = 1; i < domainCount; i++) {
                expect(config.domains[i].gtmId).toBe('GTM-SHARED');
            }
        });

        it('defaults to empty string when no GTM vars are set', () => {
            delete process.env.GTM_ID;
            for (let i = 1; i <= domainCount; i++) {
                delete process.env[`GTM_ID_${i}`];
            }
            const config = buildPublicConfig();

            for (let i = 0; i < domainCount; i++) {
                expect(config.domains[i].gtmId).toBe('');
            }
        });
    });

    describe('domain config completeness', () => {
        it('returns at least 1 domain', () => {
            const config = buildPublicConfig();

            expect(config.domains.length).toBeGreaterThanOrEqual(1);
        });

        it('first domain has correct base values', () => {
            const config = buildPublicConfig();

            expect(config.domains[0].defaultLocale).toBe('en');
            expect(config.domains[0].currencyCode).toBe('EUR');
            expect(config.domains[0].domainId).toBe(1);
            expect(config.domains[0].type).toBe('B2C');
        });

        it('all domains have correct fallbackTimezone', () => {
            const config = buildPublicConfig();

            for (const domain of config.domains) {
                expect(domain.fallbackTimezone).toBe('Europe/Prague');
            }
        });

        it('mapSetting values are numeric types', () => {
            const config = buildPublicConfig();

            for (const domain of config.domains) {
                expect(typeof domain.mapSetting.latitude).toBe('number');
                expect(typeof domain.mapSetting.longitude).toBe('number');
                expect(typeof domain.mapSetting.zoom).toBe('number');
            }
        });

        it('all domains have required fields', () => {
            const config = buildPublicConfig();

            for (const domain of config.domains) {
                expect(domain.domainId).toBeGreaterThan(0);
                expect(domain.defaultLocale).toBeTruthy();
                expect(domain.currencyCode).toBeTruthy();
                expect(domain.type).toBeTruthy();
                expect(domain.packeteryCountry).toBeTruthy();
            }
        });
    });
});
