import { buildPublicConfig } from 'buildPublicEnvConfig';
import type { getPublicConfigProperty as GetPublicConfigPropertyFn, PublicRuntimeConfig } from 'envConfig';
import { serializeConfigForHtml } from 'envConfig';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { defaultTestConfig } from 'vitest/helpers/mockPublicConfig';

describe('config robustness', () => {
    describe('prototype pollution prevention', () => {
        it('__proto__ key in JSON does not pollute Object prototype', () => {
            const malicious = '{"__proto__":{"polluted":"yes"},"cdnDomain":"safe"}';
            const parsed = JSON.parse(malicious) as Record<string, unknown>;

            expect(({} as Record<string, unknown>).polluted).toBeUndefined();
            expect(Object.hasOwn(parsed, '__proto__')).toBe(true);
            expect(Reflect.get(parsed, '__proto__')).toEqual({ polluted: 'yes' });
        });

        it('constructor.prototype in serialized config does not pollute', () => {
            const config: PublicRuntimeConfig = {
                ...defaultTestConfig,
                cdnDomain: '{"constructor":{"prototype":{"polluted":"yes"}}}',
            };
            const serialized = serializeConfigForHtml(config);
            JSON.parse(serialized);

            expect(({} as Record<string, unknown>).polluted).toBeUndefined();
        });
    });

    describe('config immutability', () => {
        const originalEnv = process.env;

        let getPublicConfigProperty: typeof GetPublicConfigPropertyFn;

        beforeEach(async () => {
            vi.resetModules();
            process.env = { ...originalEnv };
            process.env.DOMAIN_HOSTNAME_1 = 'https://domain1.example.com/';
            process.env.DOMAIN_HOSTNAME_2 = 'https://domain2.example.com/';
            process.env.DOMAIN_HOSTNAME_3 = 'https://domain3.example.com/';
            window.__ENV = { ...defaultTestConfig };

            const mod = await import('envConfig');
            getPublicConfigProperty = mod.getPublicConfigProperty;
        });

        afterEach(() => {
            process.env = originalEnv;
        });

        it('replacing window.__ENV after first access does not affect cached reads', () => {
            const originalValue = getPublicConfigProperty('cdnDomain');

            // Replace window.__ENV entirely
            const mutableEnv = { ...defaultTestConfig, cdnDomain: 'mutated' };
            window.__ENV = mutableEnv as typeof window.__ENV;

            // Direct access sees the mutation
            expect(window.__ENV?.cdnDomain).toBe('mutated');

            // API access returns cached (original) value
            expect(getPublicConfigProperty('cdnDomain')).toBe(originalValue);
        });

        it('buildPublicConfig() returns fresh objects each call', () => {
            const config1 = buildPublicConfig();
            const config2 = buildPublicConfig();

            expect(config1).not.toBe(config2);
            expect(config1).toEqual(config2);
        });

        it('mutations to one buildPublicConfig result do not affect subsequent calls', () => {
            const config1 = buildPublicConfig() as unknown as Record<string, unknown>;
            config1.cdnDomain = 'mutated';

            const config2 = buildPublicConfig();

            expect(config2.cdnDomain).not.toBe('mutated');
        });
    });

    describe('extreme inputs', () => {
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

        it('whitespace-only non-trimmed fields are preserved as-is', () => {
            process.env.CDN_DOMAIN = '   ';
            const config = buildPublicConfig();

            // CDN_DOMAIN uses ?? '' — whitespace string is truthy, so it stays
            expect(config.cdnDomain).toBe('   ');
        });

        it('"null" literal string is preserved', () => {
            process.env.CDN_DOMAIN = 'null';
            const config = buildPublicConfig();

            expect(config.cdnDomain).toBe('null');
            expect(config.cdnDomain).not.toBeNull();
        });

        it('"undefined" literal string is preserved', () => {
            process.env.CDN_DOMAIN = 'undefined';
            const config = buildPublicConfig();

            expect(config.cdnDomain).toBe('undefined');
            expect(config.cdnDomain).not.toBeUndefined();
        });

        it('"0" string is truthy for ?? operator, so it is preserved', () => {
            process.env.CDN_DOMAIN = '0';
            const config = buildPublicConfig();

            // '0' is falsy in JS but not nullish, so ?? '' keeps it
            expect(config.cdnDomain).toBe('0');
        });
    });
});
