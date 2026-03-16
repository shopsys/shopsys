import { buildPublicConfig } from 'buildPublicEnvConfig';
import { describe, it, expect, beforeEach, afterEach } from 'vitest';

describe('buildPublicConfig', () => {
    const originalEnv = process.env;

    beforeEach(() => {
        process.env = { ...originalEnv };
        process.env.DOMAIN_HOSTNAME_1 = 'https://domain1.example.com/';
        process.env.DOMAIN_HOSTNAME_2 = 'https://domain2.example.com/';
        process.env.DOMAIN_HOSTNAME_3 = 'https://domain3.example.com/';
    });

    afterEach(() => {
        process.env = originalEnv;
    });

    describe('domain URL validation', () => {
        it('throws when DOMAIN_HOSTNAME_1 is missing', () => {
            delete process.env.DOMAIN_HOSTNAME_1;

            expect(() => buildPublicConfig()).toThrow('DOMAIN_HOSTNAME_1 is required but not set.');
        });

        it('throws when DOMAIN_HOSTNAME_2 is missing', () => {
            delete process.env.DOMAIN_HOSTNAME_2;

            expect(() => buildPublicConfig()).toThrow('DOMAIN_HOSTNAME_2 is required but not set.');
        });

        it('throws when DOMAIN_HOSTNAME_3 is missing', () => {
            delete process.env.DOMAIN_HOSTNAME_3;

            expect(() => buildPublicConfig()).toThrow('DOMAIN_HOSTNAME_3 is required but not set.');
        });

        it('throws when domain URL is not a valid URL', () => {
            process.env.DOMAIN_HOSTNAME_1 = 'not-a-url';

            expect(() => buildPublicConfig()).toThrow('DOMAIN_HOSTNAME_1="not-a-url" is not a valid URL.');
        });

        it('throws for URL without protocol', () => {
            process.env.DOMAIN_HOSTNAME_2 = 'example.com';

            expect(() => buildPublicConfig()).toThrow('DOMAIN_HOSTNAME_2="example.com" is not a valid URL.');
        });

        it('accepts valid URLs with different protocols', () => {
            process.env.DOMAIN_HOSTNAME_1 = 'https://example.com/';
            process.env.DOMAIN_HOSTNAME_2 = 'http://localhost:8000/';
            process.env.DOMAIN_HOSTNAME_3 = 'https://shop.example.com/sk/';

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
            // Clear all env vars
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
        it('parses comma-separated list "1,2,3"', () => {
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = '1,2,3';
            const config = buildPublicConfig();

            expect(config.domains[0].isLuigisBoxActive).toBe(true);
            expect(config.domains[1].isLuigisBoxActive).toBe(true);
            expect(config.domains[2].isLuigisBoxActive).toBe(true);
        });

        it('empty string disables all domains', () => {
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = '';
            const config = buildPublicConfig();

            expect(config.domains[0].isLuigisBoxActive).toBe(false);
            expect(config.domains[1].isLuigisBoxActive).toBe(false);
            expect(config.domains[2].isLuigisBoxActive).toBe(false);
        });

        it('trailing comma does not cause issues', () => {
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = '1,';
            const config = buildPublicConfig();

            expect(config.domains[0].isLuigisBoxActive).toBe(true);
            expect(config.domains[1].isLuigisBoxActive).toBe(false);
        });

        it('spaces around numbers: "1, 2" does NOT match "2" via exact includes', () => {
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = '1, 2';
            const config = buildPublicConfig();

            // '1, 2'.split(',') → ['1', ' 2'] — ' 2' !== '2'
            expect(config.domains[0].isLuigisBoxActive).toBe(true);
            expect(config.domains[1].isLuigisBoxActive).toBe(false);
        });

        it('single ID "2" activates only domain 2', () => {
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = '2';
            const config = buildPublicConfig();

            expect(config.domains[0].isLuigisBoxActive).toBe(false);
            expect(config.domains[1].isLuigisBoxActive).toBe(true);
            expect(config.domains[2].isLuigisBoxActive).toBe(false);
        });

        it('undefined env var disables all domains', () => {
            delete process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS;
            const config = buildPublicConfig();

            expect(config.domains[0].isLuigisBoxActive).toBe(false);
            expect(config.domains[1].isLuigisBoxActive).toBe(false);
            expect(config.domains[2].isLuigisBoxActive).toBe(false);
        });
    });

    describe('domain 3 correctly checks .includes("3")', () => {
        it('domain 3 is NOT active when only "2" is in the list', () => {
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = '2';
            const config = buildPublicConfig();

            expect(config.domains[2].isLuigisBoxActive).toBe(false);
        });

        it('domain 3 IS active when only "3" is in the list', () => {
            process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS = '3';
            const config = buildPublicConfig();

            expect(config.domains[2].isLuigisBoxActive).toBe(true);
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
            process.env.GTM_ID_1 = 'GTM-DOMAIN1';
            process.env.GTM_ID_2 = 'GTM-DOMAIN2';
            process.env.GTM_ID_3 = 'GTM-DOMAIN3';
            const config = buildPublicConfig();

            expect(config.domains[0].gtmId).toBe('GTM-DOMAIN1');
            expect(config.domains[1].gtmId).toBe('GTM-DOMAIN2');
            expect(config.domains[2].gtmId).toBe('GTM-DOMAIN3');
        });

        it('falls back to shared GTM_ID when per-domain vars are not set', () => {
            process.env.GTM_ID = 'GTM-SHARED';
            delete process.env.GTM_ID_1;
            delete process.env.GTM_ID_2;
            delete process.env.GTM_ID_3;
            const config = buildPublicConfig();

            expect(config.domains[0].gtmId).toBe('GTM-SHARED');
            expect(config.domains[1].gtmId).toBe('GTM-SHARED');
            expect(config.domains[2].gtmId).toBe('GTM-SHARED');
        });

        it('per-domain GTM_ID_N takes priority over shared GTM_ID', () => {
            process.env.GTM_ID = 'GTM-SHARED';
            process.env.GTM_ID_1 = 'GTM-OVERRIDE';
            delete process.env.GTM_ID_2;
            delete process.env.GTM_ID_3;
            const config = buildPublicConfig();

            expect(config.domains[0].gtmId).toBe('GTM-OVERRIDE');
            expect(config.domains[1].gtmId).toBe('GTM-SHARED');
            expect(config.domains[2].gtmId).toBe('GTM-SHARED');
        });

        it('defaults to empty string when no GTM vars are set', () => {
            delete process.env.GTM_ID;
            delete process.env.GTM_ID_1;
            delete process.env.GTM_ID_2;
            delete process.env.GTM_ID_3;
            const config = buildPublicConfig();

            expect(config.domains[0].gtmId).toBe('');
            expect(config.domains[1].gtmId).toBe('');
            expect(config.domains[2].gtmId).toBe('');
        });
    });

    describe('domain config completeness', () => {
        it('returns exactly 3 domains', () => {
            const config = buildPublicConfig();

            expect(config.domains).toHaveLength(3);
        });

        it('domains have correct hardcoded values', () => {
            const config = buildPublicConfig();

            expect(config.domains[0].defaultLocale).toBe('en');
            expect(config.domains[0].currencyCode).toBe('EUR');
            expect(config.domains[0].domainId).toBe(1);
            expect(config.domains[0].type).toBe('B2C');

            expect(config.domains[1].defaultLocale).toBe('cs');
            expect(config.domains[1].currencyCode).toBe('CZK');
            expect(config.domains[1].domainId).toBe(2);
            expect(config.domains[1].type).toBe('B2B');

            expect(config.domains[2].defaultLocale).toBe('sk');
            expect(config.domains[2].currencyCode).toBe('EUR');
            expect(config.domains[2].domainId).toBe(3);
            expect(config.domains[2].type).toBe('B2B');
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
    });
});
