import path from 'node:path';
import { defineConfig } from '@rsbuild/core';
import { pluginSass } from '@rsbuild/plugin-sass';
import Symfony from '@symfony/reprise/rsbuild';
import sources from './assets/js/bin/helpers/sources';

export default defineConfig({
    server: {
        // reuses the port that docker-compose already exposes on the php-fpm container (formerly LiveReload)
        port: 35729,
    },
    source: {
        entry: {
            admin: './assets/js/admin/admin.js',
            administration: `${sources.getPackageNodeModulesDir('administration')}/src/js/index.js`,
            'admin-style': './assets/styles/admin/main.scss',
        },
    },
    resolve: {
        alias: {
            framework: '@shopsys/framework/js',
            // single jQuery instance shared by all @shopsys packages, resolved from the root node_modules
            jquery: path.resolve(sources.getNodeModulesDir(), 'jquery'),
            'bazinga-translator': path.resolve(sources.getNodeModulesDir(), 'bazinga-translator'),
            icons: path.resolve(__dirname, 'assets/icons'),
        },
    },
    tools: {
        rspack: {
            optimization: {
                // share one runtime and dedupe modules across the admin and administration entries,
                // otherwise jQuery, Register, and Tabler are instantiated once per entry (Encore's
                // splitEntryChunks + enableSingleRuntimeChunk equivalent)
                runtimeChunk: 'single',
                splitChunks: {
                    chunks: 'all',
                },
            },
        },
    },
    plugins: [
        pluginSass({
            sassLoaderOptions: {
                sassOptions: {
                    quietDeps: true,
                    silenceDeprecations: ['import'],
                },
            },
        }),
        Symfony({
            outputPath: 'web/build',
            publicPath: `${process.env.CDN_DOMAIN ?? ''}/build`,
            manifestKeyPrefix: 'web',
            stimulus: {
                controllersJson: './assets/controllers.json',
                controllersDir: './assets/controllers',
            },
            copy: [
                {
                    from: `${sources.getPackageNodeModulesDir('framework')}/public/admin`,
                    to: '../public/admin',
                    hash: false,
                },
                { from: 'assets/public', to: '../public', hash: false },
                { from: 'assets/extra', to: '..', hash: false },
            ],
        }),
    ],
});
