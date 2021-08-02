/* eslint-disable @typescript-eslint/no-var-requires */
const { version } = require('../package.json');
const path = require('path');

module.exports = {
    title: 'Storefront Styleguide',
    version,
    styleguideDir: '../docs/styleguide/',
    exampleMode: 'expand',
    usageMode: 'expand',
    template: {
        favicon: 'https://www.shopsys.com/favicon.ico',
    },
    styleguideComponents: {
        Wrapper: path.join(__dirname, '../context/ShopsysGlobalProvider'),
    },
    propsParser: require('react-docgen-typescript').withCustomConfig('./tsconfig.json', {
        compilerOptions: { noEmit: false },
    }).parse,
    resolver: require('react-docgen').resolver.findAllComponentDefinitions,
    sections: [
        {
            name: 'Introduction',
            content: '../docs/index.md',
            sections: [
                {
                    name: 'React Packages',
                    content: '../docs/react-packages.md',
                },
                {
                    name: 'Coding standards',
                    content: '../docs/coding-standards.md',
                },
                {
                    name: 'ESlint rules',
                    content: '../docs/eslint-rules.md',
                },
                {
                    name: 'Styled-components',
                    content: '../docs/styled-components.md',
                },
                {
                    name: 'TypeScript',
                    content: '../docs/typescript.md',
                },
                {
                    name: 'Basic principles',
                    content: '../docs/basic-principles.md',
                },
                {
                    name: 'New component',
                    content: '../docs/new-component.md',
                },
            ],
        },
        {
            name: 'UI Components',
            content: '../docs/components.md',
            components: '../components/**/*.{tsx,js}',
            ignore: [
                '../components/**/*.style.{ts,js}',
                '../components/**/index.{ts,js}',
                '../components/blocks/**/*.*',
            ],
        },
        {
            name: 'Blocks',
            content: '../docs/blocks.md',
            components: '../components/blocks/**/*.{tsx,js}',
        },
        {
            name: 'Global Providers',
            content: '../docs/global-providers.md',
            components: '../context/**/*.{tsx,js}',
            ignore: '../context/**/index.{ts,js}',
        },
    ],
    webpackConfig: {
        module: {
            rules: [
                {
                    test: /\.tsx?$/,
                    exclude: /node_modules/,
                    loader: 'babel-loader',
                },
            ],
        },
        devServer: {
            disableHostCheck: true,
        },
    },
    assetsDir: '../public/',
};
