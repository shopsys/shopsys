/* eslint-disable @typescript-eslint/no-require-imports */
const { withSentryConfig } = require('@sentry/nextjs');
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
        optimizePackageImports: ['@urql/core', 'framer-motion', 'react-toastify', 'lodash-es'],
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
    webpack: (config, { isServer, dev }) => {
        if (!dev && !isServer) {
            config.optimization.splitChunks = {
                chunks: 'all',
                minSize: 20000,
                maxSize: 200000, // 200KB max per chunk
                cacheGroups: {
                    vendor: {
                        test: /[\\/]node_modules[\\/]/,
                        name: 'vendors',
                        chunks: 'all',
                        maxSize: 150000, // Smaller vendor chunks
                    },
                    urql: {
                        test: /[\\/]node_modules[\\/](@urql|urql)[\\/]/,
                        name: 'urql',
                        chunks: 'all',
                        priority: 10,
                        maxSize: 100000,
                    },
                    react: {
                        test: /[\\/]node_modules[\\/](react|react-dom)[\\/]/,
                        name: 'react',
                        chunks: 'all',
                        priority: 10,
                    },
                    sentry: {
                        test: /[\\/]node_modules[\\/]@sentry[\\/]/,
                        name: 'sentry',
                        chunks: 'all',
                        priority: 10,
                        maxSize: 120000, // Limit sentry chunk size
                    },
                    // Add more granular splitting
                    ui: {
                        test: /[\\/]node_modules[\\/](framer-motion|react-toastify)[\\/]/,
                        name: 'ui-libs',
                        chunks: 'all',
                        priority: 9,
                    },
                    utils: {
                        test: /[\\/]node_modules[\\/](lodash|date-fns|uuid)[\\/]/,
                        name: 'utils',
                        chunks: 'all',
                        priority: 8,
                    },
                },
            };
        }

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
        config.ignoreWarnings = [
            ...(config.ignoreWarnings || []),
            {
                // Ignore warnings for dynamic requires in @opentelemetry/instrumentation
                module: /@opentelemetry\/instrumentation/,
                message: /Critical dependency: the request of a dependency is an expression/,
            },
            {
                // Sentry itself might have dynamic requires
                module: /@sentry\/nextjs/,
                message: /Critical dependency: the request of a dependency is an expression/,
            },
            {
                module: /@sentry\/node/,
                message: /Critical dependency: the request of a dependency is an expression/,
            },
        ];

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
        excludeTracing: process.env.NODE_ENV === 'production', // Exclude tracing in production
        // all bellow - remove (set false) if you want to use replays
        excludeReplayShadowDom: true,
        excludeReplayIframe: true,
        excludeReplayWorker: true,
        excludeReplayCanvas: true,
        excludeReplayMask: true,
        excludeReplayCompressionWorker: true,
    },
};

module.exports = withBundleAnalyzer(withSentryConfig(nextConfig), sentryConfig);
