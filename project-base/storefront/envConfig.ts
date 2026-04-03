import { CustomerUserAreaEnum } from 'types/customer';

// === Type Definitions ===

export type DomainConfig = {
    readonly url: string;
    readonly publicGraphqlEndpoint: string;
    readonly defaultLocale: string;
    readonly currencyCode: string;
    readonly fallbackTimezone: string;
    readonly domainId: number;
    readonly mapSetting: Readonly<{ latitude: number; longitude: number; zoom: number }>;
    readonly gtmId: string;
    readonly isLuigisBoxActive: boolean;
    readonly packeteryCountry: string;
    readonly type: CustomerUserAreaEnum;
};

export type PublicRuntimeConfig = {
    readonly domains: readonly DomainConfig[];
    readonly cdnDomain: string;
    readonly shouldUseDefer: boolean;
    readonly errorDebuggingLevel: string;
    readonly userSnapEnabledDefaultValue: boolean;
    readonly userSnapApiKey: string;
    readonly googleMapApiKey: string;
    readonly packeteryApiKey: string;
    readonly showSymfonyToolbar: string;
    readonly sentryDsn: string;
    readonly sentryEnvironment: string;
    readonly sentryFeedbackEnable: boolean;
    readonly sentryReplaysEnable: boolean;
};

type ServerRuntimeConfig = {
    internalGraphqlEndpoint?: string;
};

// === Public Config Access ===

// ARCHITECTURE NOTE: On the client, window.__ENV is set by a synchronous <script> tag
// in <Head> of _document.tsx, which executes before React hydration. This guarantees
// that window.__ENV is available when modules calling getPublicConfigProperty() at
// module scope initialize. This assumption holds for Next.js Pages Router; changes to
// script loading strategy (e.g., async/defer) or use in web workers would break it.

let configCache: PublicRuntimeConfig | null = null;

export function getPublicConfig(): PublicRuntimeConfig {
    if (configCache) {
        return configCache;
    }

    if (typeof window !== 'undefined') {
        if (!window.__ENV) {
            throw new Error(
                'window.__ENV is not initialized. Ensure the config script tag in _document.tsx loaded correctly.',
            );
        }

        configCache = Object.freeze(window.__ENV);
    } else {
        // Dynamic require prevents Next.js webpack from bundling buildPublicConfig (which reads
        // process.env) into the client bundle. The typeof window guard above is not sufficient
        // because Next.js Pages Router bundles both branches for the client.
        configCache = require('./buildPublicEnvConfig').buildPublicConfig() as PublicRuntimeConfig;
    }

    return configCache;
}

export function getPublicConfigProperty<K extends keyof PublicRuntimeConfig>(key: K): PublicRuntimeConfig[K] {
    return getPublicConfig()[key];
}

// === Server Config Access ===

export function getServerConfigProperty<K extends keyof ServerRuntimeConfig>(
    key: K,
): ServerRuntimeConfig[K] | undefined;

export function getServerConfigProperty<K extends keyof ServerRuntimeConfig, T>(
    key: K,
    defaultValue: T,
): Exclude<ServerRuntimeConfig[K], undefined> | T;

export function getServerConfigProperty<K extends keyof ServerRuntimeConfig, T>(
    key: K,
    defaultValue?: T,
): ServerRuntimeConfig[K] | T | undefined {
    const serverConfig: ServerRuntimeConfig = {
        internalGraphqlEndpoint: process.env.INTERNAL_ENDPOINT,
    };
    const value = serverConfig[key];

    return value !== undefined ? value : defaultValue;
}

// === Serialization ===

// XSS-safe: replaces < with \u003c to prevent </script> injection in HTML context
export function serializeConfigForHtml(config: PublicRuntimeConfig): string {
    return JSON.stringify(config).replace(/</g, '\\u003c');
}
