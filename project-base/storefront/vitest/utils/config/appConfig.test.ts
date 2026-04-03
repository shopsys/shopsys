import type {
    getPublicConfigProperty as GetPublicConfigPropertyFn,
    getServerConfigProperty as GetServerConfigPropertyFn,
} from 'envConfig';
import { afterEach, beforeEach, describe, expect, expectTypeOf, it, vi } from 'vitest';
import { defaultTestConfig } from 'vitest/helpers/mockPublicConfig';

describe('appConfig utilities', () => {
    const originalEnv = process.env;

    let getPublicConfigProperty: typeof GetPublicConfigPropertyFn;
    let getServerConfigProperty: typeof GetServerConfigPropertyFn;

    beforeEach(async () => {
        vi.resetModules();
        window.__ENV = { ...defaultTestConfig };
        process.env = { ...originalEnv };

        const mod = await import('envConfig');
        getPublicConfigProperty = mod.getPublicConfigProperty;
        getServerConfigProperty = mod.getServerConfigProperty;
    });

    afterEach(() => {
        process.env = originalEnv;
    });

    describe('getPublicConfigProperty', () => {
        it('should return specific property value from window.__ENV', () => {
            window.__ENV = { ...defaultTestConfig, cdnDomain: 'test.com', shouldUseDefer: true };
            // Re-import not needed: cache is empty, first call reads current window.__ENV
            const cdnDomain = getPublicConfigProperty('cdnDomain');
            const shouldUseDefer = getPublicConfigProperty('shouldUseDefer');

            expect(cdnDomain).toBe('test.com');
            expect(shouldUseDefer).toBe(true);
        });

        it('should have correct return type', () => {
            const result = getPublicConfigProperty('userSnapEnabledDefaultValue');

            expectTypeOf(result).toEqualTypeOf<boolean>();
        });

        it('should return the config value even when it is falsy', () => {
            window.__ENV = { ...defaultTestConfig, cdnDomain: '' };

            const result = getPublicConfigProperty('cdnDomain');

            expect(result).toBe('');
        });

        it('should read from window.__ENV on first access', () => {
            window.__ENV = { ...defaultTestConfig, cdnDomain: 'client-cdn.example.com' };

            expect(getPublicConfigProperty('cdnDomain')).toBe('client-cdn.example.com');
        });

        it('should return cached value after first access', () => {
            window.__ENV = { ...defaultTestConfig, cdnDomain: 'original.com' };
            getPublicConfigProperty('cdnDomain');

            // Mutate after first access
            window.__ENV = { ...defaultTestConfig, cdnDomain: 'mutated.com' };

            expect(getPublicConfigProperty('cdnDomain')).toBe('original.com');
        });
    });

    describe('getServerConfigProperty', () => {
        it('should return server property from process.env', () => {
            process.env.INTERNAL_ENDPOINT = 'http://internal';

            const endpoint = getServerConfigProperty('internalGraphqlEndpoint');

            expect(endpoint).toBe('http://internal');
        });

        it('should return default value when server property is undefined', () => {
            delete process.env.INTERNAL_ENDPOINT;

            const result = getServerConfigProperty('internalGraphqlEndpoint', 'http://default');

            expect(result).toBe('http://default');
        });

        it('should have return type without undefined when default value is provided', () => {
            const result = getServerConfigProperty('internalGraphqlEndpoint', 'http://default');

            expectTypeOf(result).toEqualTypeOf<string>();
        });
    });
});
