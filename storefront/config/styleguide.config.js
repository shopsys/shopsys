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
        Wrapper: path.join(__dirname, '../components/SsfwGlobalProvider'),
    },
    propsParser(filePath, source, resolver, handlers) {
        return require('react-docgen').parse(source, resolver, handlers);
    },
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
            components: '../components/**/*.js',
            ignore: ['../components/**/*.style.js', '../components/**/index.js'],
        },
    ],
    webpackConfig: {
        module: {
            rules: [
                {
                    test: /\.jsx?$/,
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
