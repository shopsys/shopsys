import getConfig from 'next/config';

/**
 * Safely access Next.js runtime configuration.
 *
 * getConfig() returns undefined in non-Next.js environments (like Cypress tests),
 * so this utility provides safe access with proper error handling and defaults.
 */

export interface NextConfigPublicRuntimeConfig {
    domains?: Array<{
        url: string;
        publicGraphqlEndpoint: string;
        defaultLocale: string;
        currencyCode: string;
        fallbackTimezone: string;
        domainId: number;
        mapSetting: {
            latitude: number;
            longitude: number;
            zoom: number;
        };
        gtmId?: string;
        isLuigisBoxActive: boolean;
        type: string;
    }>;
    cdnDomain?: string;
    shouldUseDefer?: boolean;
    errorDebuggingLevel?: string;
    userSnapEnabledDefaultValue?: boolean;
    userSnapApiKey?: string;
    googleMapApiKey?: string;
    packeteryApiKey?: string;
    showSymfonyToolbar?: string;
    sentryDsn?: string;
    sentryEnvironment?: string;
    sentryFeedbackEnable?: boolean;
    sentryReplaysEnable?: boolean;
    [key: string]: any;
}

export interface NextConfigServerRuntimeConfig {
    internalGraphqlEndpoint?: string;
    [key: string]: any;
}

export interface NextConfig {
    publicRuntimeConfig?: NextConfigPublicRuntimeConfig;
    serverRuntimeConfig?: NextConfigServerRuntimeConfig;
}

export function getNextConfig(): NextConfig {
    try {
        const config = getConfig();
        return config || {};
    } catch {
        return {};
    }
}

export function getPublicConfigProperty<K extends keyof NextConfigPublicRuntimeConfig>(
    key: K,
): NextConfigPublicRuntimeConfig[K] | undefined;

export function getPublicConfigProperty<K extends keyof NextConfigPublicRuntimeConfig, T>(
    key: K,
    defaultValue: T,
): NextConfigPublicRuntimeConfig[K] | T;

export function getPublicConfigProperty<K extends keyof NextConfigPublicRuntimeConfig, T>(
    key: K,
    defaultValue?: T,
): NextConfigPublicRuntimeConfig[K] | T | undefined {
    const config = getNextConfig();
    const publicConfig = config.publicRuntimeConfig || {};
    const value = publicConfig[key];
    return value !== undefined ? value : defaultValue;
}
export function getServerConfigProperty<K extends keyof NextConfigServerRuntimeConfig>(
    key: K,
): NextConfigServerRuntimeConfig[K] | undefined;

export function getServerConfigProperty<K extends keyof NextConfigServerRuntimeConfig, T>(
    key: K,
    defaultValue: T,
): NextConfigServerRuntimeConfig[K] | T;

export function getServerConfigProperty<K extends keyof NextConfigServerRuntimeConfig, T>(
    key: K,
    defaultValue?: T,
): NextConfigServerRuntimeConfig[K] | T | undefined {
    const config = getNextConfig();
    const serverConfig = config.serverRuntimeConfig || {};
    const value = serverConfig[key];
    return value !== undefined ? value : defaultValue;
}
