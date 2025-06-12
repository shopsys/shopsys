/* eslint-disable @typescript-eslint/no-require-imports */
const { withSentryConfig } = require('@sentry/nextjs');
const nextTranslate = require('next-translate-plugin');
const withBundleAnalyzer = require('@next/bundle-analyzer')({
    enabled: process.env.ANALYZE === 'true',
});

// Sentry feature flags
const isSentryReplaysEnabled = process.env.SENTRY_REPLAYS_ENABLE === '1';
const isSentryFeedbackEnabled = process.env.SENTRY_FEEDBACK_ENABLE === '1';

/** @type {import('next').NextConfig} */
const nextConfig = {
    experimental: {
        scrollRestoration: true,
        middlewarePrefetch: 'strict',
    },
    reactStrictMode: true,
    assetPrefix: process.env.CDN_DOMAIN ?? undefined,
    images: {
        loader: 'custom',
        deviceSizes: [480, 768, 1024, 1440], // Do not forget to update the same values in the `app/web/imageResizer.php` file
        imageSizes: [16, 24, 32, 48, 64, 96, 128, 256],
        remotePatterns: [
            {
                hostname: process.env.DOMAIN_HOSTNAME_1,
            },
            {
                hostname: process.env.DOMAIN_HOSTNAME_2,
            },
        ],
    },
    serverRuntimeConfig: {
        internalGraphqlEndpoint: `${process.env.INTERNAL_ENDPOINT}graphql/`,
    },
    publicRuntimeConfig: {
        googleMapApiKey: process.env.GOOGLE_MAP_API_KEY,
        packeteryApiKey: process.env.PACKETERY_API_KEY,
        cdnDomain: process.env.CDN_DOMAIN ?? '',
        sentryDsn: process.env.SENTRY_DSN ?? '',
        sentryEnvironment: process.env.SENTRY_ENVIRONMENT ?? '',
        sentryFeedbackEnable: isSentryFeedbackEnabled,
        sentryReplaysEnable: isSentryReplaysEnabled,
        errorDebuggingLevel: process.env.ERROR_DEBUGGING_LEVEL,
        showSymfonyToolbar: process.env.SHOW_SYMFONY_TOOLBAR,
        shouldUseDefer: process.env.SHOULD_USE_DEFER === '1',
        userSnapApiKey: process.env.USERSNAP_PROJECT_API_KEY,
        userSnapEnabledDefaultValue: process.env.USERSNAP_STOREFRONT_ENABLED_BY_DEFAULT === '1',
        domains: [
            {
                publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_1,
                url: process.env.DOMAIN_HOSTNAME_1,
                defaultLocale: 'en',
                currencyCode: 'EUR',
                fallbackTimezone: 'Europe/Prague',
                domainId: 1,
                mapSetting: {
                    latitude: 49.8175,
                    longitude: 15.473,
                    zoom: 7,
                },
                gtmId: process.env.GTM_ID,
                isLuigisBoxActive: (process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS ?? '').split(',').includes('1'),
                type: 'B2C',
            },
            {
                publicGraphqlEndpoint: process.env.PUBLIC_GRAPHQL_ENDPOINT_HOSTNAME_2,
                url: process.env.DOMAIN_HOSTNAME_2,
                defaultLocale: 'cs',
                currencyCode: 'CZK',
                fallbackTimezone: 'Europe/Prague',
                domainId: 2,
                mapSetting: {
                    latitude: 48.669,
                    longitude: 19.699,
                    zoom: 7,
                },
                gtmId: process.env.GTM_ID,
                isLuigisBoxActive: (process.env.LUIGIS_BOX_ENABLED_DOMAIN_IDS ?? '').split(',').includes('2'),
                type: 'B2B',
            },
        ],
    },
    eslint: {
        ignoreDuringBuilds: true,
    },
    // FE build error fix: "ModuleNotFoundError: Module not found: Error: Can't resolve 'net' in '/app/node_modules/@node-redis/client/dist/lib/client'"
    // https://github.com/webpack-contrib/css-loader/issues/447#issuecomment-761853289
    webpack: (config, { isServer }) => {
        config.resolve.fallback = {
            child_process: false,
            fs: false,
            util: false,
            http: false,
            https: false,
            tls: false,
            net: false,
            crypto: false,
            path: false,
            os: false,
            stream: false,
            zlib: false,
        };
        if (!isServer) {
            config.resolve.alias.redis = false;
        }

        return config;
    },
};

/**
 * @type {import('@sentry/nextjs/build/types/config/types').SentryBuildOptions}
 */
const sentryConfig = {
    authToken: process.env.SENTRY_AUTH_TOKEN,
    telemetry: false,
    unstable_sentryWebpackPluginOptions: {
        disable: process.env.APP_ENV === 'development',
        errorHandler: (err) => {
            // eslint-disable-next-line no-console
            console.warn('Sentry CLI Plugin: ' + err.message);
        },
    },
    sourcemaps: {
        deleteSourcemapsAfterUpload: true,
    },

    widenClientFileUpload: true,
    reactComponentAnnotation: {
        enabled: true,
    },
    disableLogger: true,
    bundleSizeOptimizations: {
        excludeDebugStatements: true,
        excludeTracing: process.env.APP_ENV === 'development',
        // Exclude replay code from bundle when replays are disabled
        excludeReplayShadowDom: !isSentryReplaysEnabled,
        excludeReplayIframe: !isSentryReplaysEnabled,
        excludeReplayWorker: !isSentryReplaysEnabled,
    },
};

module.exports = withBundleAnalyzer(withSentryConfig(nextTranslate(nextConfig), sentryConfig));
