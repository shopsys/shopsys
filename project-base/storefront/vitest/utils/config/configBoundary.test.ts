import { buildPublicConfig } from 'buildPublicEnvConfig';
import { PublicRuntimeConfig, serializeConfigForScriptTag } from 'envConfig';
import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { defaultTestConfig } from 'vitest/helpers/mockPublicConfig';

describe('SSR/CSR boundary', () => {
    describe('server-only value isolation', () => {
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

        it('INTERNAL_ENDPOINT never appears in public config JSON', () => {
            process.env.INTERNAL_ENDPOINT = 'http://internal-secret:8000/graphql';

            const config = buildPublicConfig();
            const serialized = serializeConfigForScriptTag(config);

            expect(serialized).not.toContain('internal-secret');
            expect(serialized).not.toContain('INTERNAL_ENDPOINT');
        });
    });

    describe('serialization roundtrip type preservation', () => {
        it('booleans stay boolean after serialize/parse', () => {
            const config: PublicRuntimeConfig = {
                ...defaultTestConfig,
                shouldUseDefer: true,
                sentryFeedbackEnable: false,
            };
            const parsed = JSON.parse(serializeConfigForScriptTag(config)) as PublicRuntimeConfig;

            expect(typeof parsed.shouldUseDefer).toBe('boolean');
            expect(parsed.shouldUseDefer).toBe(true);
            expect(typeof parsed.sentryFeedbackEnable).toBe('boolean');
            expect(parsed.sentryFeedbackEnable).toBe(false);
        });

        it('numbers stay number after serialize/parse', () => {
            const config: PublicRuntimeConfig = { ...defaultTestConfig };
            const parsed = JSON.parse(serializeConfigForScriptTag(config)) as PublicRuntimeConfig;

            expect(typeof parsed.domains[0].mapSetting.latitude).toBe('number');
            expect(typeof parsed.domains[0].mapSetting.zoom).toBe('number');
            expect(typeof parsed.domains[0].domainId).toBe('number');
        });

        it('arrays stay arrays after serialize/parse', () => {
            const config: PublicRuntimeConfig = { ...defaultTestConfig };
            const parsed = JSON.parse(serializeConfigForScriptTag(config)) as PublicRuntimeConfig;

            expect(Array.isArray(parsed.domains)).toBe(true);
            expect(parsed.domains).toHaveLength(defaultTestConfig.domains.length);
        });

        it('objects maintain structure after serialize/parse', () => {
            const config: PublicRuntimeConfig = { ...defaultTestConfig };
            const parsed = JSON.parse(serializeConfigForScriptTag(config)) as PublicRuntimeConfig;

            expect(parsed.domains[0].mapSetting).toEqual(defaultTestConfig.domains[0].mapSetting);
        });

        it('empty gtmId stays as empty string after serialize/parse', () => {
            const config: PublicRuntimeConfig = {
                ...defaultTestConfig,
                domains: [{ ...defaultTestConfig.domains[0], gtmId: '' }],
            };
            const parsed = JSON.parse(serializeConfigForScriptTag(config)) as PublicRuntimeConfig;

            expect(parsed.domains[0].gtmId).toBe('');
        });

        it('empty strings stay empty (not null/undefined)', () => {
            const config: PublicRuntimeConfig = { ...defaultTestConfig, cdnDomain: '', sentryDsn: '' };
            const parsed = JSON.parse(serializeConfigForScriptTag(config)) as PublicRuntimeConfig;

            expect(parsed.cdnDomain).toBe('');
            expect(parsed.sentryDsn).toBe('');
            expect(parsed.cdnDomain).not.toBeNull();
            expect(parsed.cdnDomain).not.toBeUndefined();
        });
    });

    describe('client config consistency', () => {
        it('multiple reads of window.__ENV return the same reference', () => {
            window.__ENV = { ...defaultTestConfig };

            const first = window.__ENV;
            const second = window.__ENV;

            expect(first).toBe(second);
        });
    });
});
