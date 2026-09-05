const withSentryConfig = require('@sentry/nextjs').withSentryConfig;
const nextTranslate = require('next-translate-plugin');
const withBundleAnalyzer = require('@next/bundle-analyzer')({
    enabled: process.env.ANALYZE === 'true',
});
const { SENTRY_APPLICATION_KEY } = require('./sentryApplicationKey');

// Sentry feature flags
const isSentryReplaysEnabled = process.env.SENTRY_REPLAYS_ENABLE === '1';
const isSentryBuildPluginEnabled =
    process.env.SENTRY_BUILD_PLUGIN_DISABLED !== '1' &&
    !!process.env.SENTRY_AUTH_TOKEN &&
    !!process.env.SENTRY_ORG &&
    !!process.env.SENTRY_PROJECT;

/** @type {import('next').NextConfig} */
const nextConfig = {
    experimental: {
        reactCompiler: true,
        scrollRestoration: true,
        middlewarePrefetch: 'strict',
    },
    reactStrictMode: true,
    assetPrefix: process.env.CDN_DOMAIN ?? undefined,
    rewrites: async () => [
        {
            source: '/.well-known/security.txt',
            destination: '/security.txt',
        },
    ],
    images: {
        loader: 'custom',
        deviceSizes: [480, 768, 1024, 1440, 1920], // Do not forget to update the same values in the `app/web/imageResizer.php` file
        imageSizes: [16, 24, 32, 48, 64, 96, 128, 256, 384],
        remotePatterns: [
            {
                hostname: process.env.DOMAIN_HOSTNAME_1,
            },
            {
                hostname: process.env.DOMAIN_HOSTNAME_2,
            },
            {
                hostname: process.env.DOMAIN_HOSTNAME_3,
            },
        ],
    },

    compiler: {
        reactRemoveProperties: process.env.CYPRESS_KEEP_TID === '1' ? false : { properties: ['^data-tid$'] },
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

        // Suppress "Critical dependency" warnings from OpenTelemetry's require-in-the-middle
        // (used by Sentry for instrumentation). These dynamic requires are intentional and harmless.
        config.ignoreWarnings = [...(config.ignoreWarnings ?? []), { module: /require-in-the-middle/ }];

        return config;
    },
};

/**
 * @type {import('@sentry/nextjs/build/types/config/types').SentryBuildOptions}
 */
const sentryConfig = {
    org: process.env.SENTRY_ORG,
    project: process.env.SENTRY_PROJECT,
    authToken: process.env.SENTRY_AUTH_TOKEN,
    sentryUrl: process.env.SENTRY_URL,
    applicationKey: SENTRY_APPLICATION_KEY,
    telemetry: false,
    release: {
        name: process.env.SENTRY_RELEASE,
    },
    unstable_sentryWebpackPluginOptions: {
        disable: process.env.APP_ENV === 'development',
        errorHandler: (err) => {
            // biome-ignore lint/suspicious/noConsole: Sentry build integration intentionally reports plugin issues to the terminal.
            console.warn(`Sentry CLI Plugin: ${err.message}`);
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

const translatedNextConfig = nextTranslate(nextConfig);
const nextConfigWithSentryIfEnabled = isSentryBuildPluginEnabled
    ? withSentryConfig(translatedNextConfig, sentryConfig)
    : translatedNextConfig;

module.exports = withBundleAnalyzer(nextConfigWithSentryIfEnabled);
