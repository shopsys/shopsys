import getConfig from 'next/config';
import { getNextConfig, getPublicConfigProperty, getServerConfigProperty } from 'utils/config/getNextConfig';
import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';

vi.mock('next/config');

const mockGetConfig = vi.mocked(getConfig);

describe('getNextConfig utilities', () => {
    beforeEach(() => {
        vi.clearAllMocks();
    });

    afterEach(() => {
        vi.resetAllMocks();
    });

    describe('getNextConfig', () => {
        it('should return config when getConfig succeeds', () => {
            const mockConfig = {
                publicRuntimeConfig: { domains: [], cdnDomain: 'test.com' },
                serverRuntimeConfig: { internalGraphqlEndpoint: 'http://internal' },
            };
            mockGetConfig.mockReturnValue(mockConfig);

            const result = getNextConfig();

            expect(result).toEqual(mockConfig);
            expect(mockGetConfig).toHaveBeenCalledOnce();
        });

        it('should return empty object when getConfig returns null/undefined', () => {
            mockGetConfig.mockReturnValue(null);

            const result = getNextConfig();

            expect(result).toEqual({});
        });

        it('should return empty object when getConfig throws error', () => {
            mockGetConfig.mockImplementation(() => {
                throw new Error('getConfig failed');
            });

            const result = getNextConfig();

            expect(result).toEqual({});
        });
    });

    describe('getPublicConfigProperty', () => {
        it('should return specific property value when available', () => {
            mockGetConfig.mockReturnValue({
                publicRuntimeConfig: {
                    cdnDomain: 'test.com',
                    shouldUseDefer: true,
                },
            });

            const cdnDomain = getPublicConfigProperty('cdnDomain');
            const shouldUseDefer = getPublicConfigProperty('shouldUseDefer');

            expect(cdnDomain).toBe('test.com');
            expect(shouldUseDefer).toBe(true);
        });

        it('should return default value when property is undefined', () => {
            mockGetConfig.mockReturnValue({
                publicRuntimeConfig: {},
            });

            const result = getPublicConfigProperty('cdnDomain', 'default.com');

            expect(result).toBe('default.com');
        });

        it('should return undefined when property and default are not provided', () => {
            mockGetConfig.mockReturnValue({
                publicRuntimeConfig: {},
            });

            const result = getPublicConfigProperty('cdnDomain');

            expect(result).toBeUndefined();
        });
    });

    describe('getServerConfigProperty', () => {
        it('should return specific server property value when available', () => {
            mockGetConfig.mockReturnValue({
                serverRuntimeConfig: {
                    internalGraphqlEndpoint: 'http://internal',
                },
            });

            const endpoint = getServerConfigProperty('internalGraphqlEndpoint');

            expect(endpoint).toBe('http://internal');
        });

        it('should return default value when server property is undefined', () => {
            mockGetConfig.mockReturnValue({
                serverRuntimeConfig: {},
            });

            const result = getServerConfigProperty('internalGraphqlEndpoint', 'http://default');

            expect(result).toBe('http://default');
        });
    });
});
