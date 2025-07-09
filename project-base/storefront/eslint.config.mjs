import { fixupPluginRules } from '@eslint/compat';
import { FlatCompat } from '@eslint/eslintrc';
import js from '@eslint/js';
import typescriptEslint from '@typescript-eslint/eslint-plugin';
import tsParser from '@typescript-eslint/parser';
import jsxA11y from 'eslint-plugin-jsx-a11y';
import noRelativeImportPaths from 'eslint-plugin-no-relative-import-paths';
import react from 'eslint-plugin-react';
import reactHooks from 'eslint-plugin-react-hooks';
import unusedImports from 'eslint-plugin-unused-imports';
import globals from 'globals';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
    allConfig: js.configs.all,
});

export default [
    {
        ignores: [
            'node_modules/*',
            'cypress/*',
            '.next/*',
            'public/*',
            '!**/.prettierrc.js',
            'graphql/types.ts',
            '**/*.generated.*',
            'config/*',
            '**/eslint.config.mjs',
            '**/schema.graphql.json',
            '**/schema-compressed.graphql.json',
            '**/pnpm-lock.json',
            '**/package.json',
            '**/tsconfig.json',
            '.pnpm-store/*',
            '**/eslint-rules',
        ],
    },
    ...compat.extends(
        'eslint:recommended',
        'plugin:react/recommended',
        'plugin:jsx-a11y/recommended',
        'plugin:@typescript-eslint/eslint-recommended',
        'plugin:@typescript-eslint/recommended',
        'prettier',
    ),
    {
        plugins: {
            react,
            'unused-imports': unusedImports,
            '@typescript-eslint': typescriptEslint,
            'react-hooks': fixupPluginRules(reactHooks),
            'no-relative-import-paths': noRelativeImportPaths,
            'jsx-a11y': jsxA11y,
        },

        languageOptions: {
            globals: {
                ...globals.browser,
            },

            parser: tsParser,
            ecmaVersion: 12,
            sourceType: 'module',

            parserOptions: {
                ecmaFeatures: {
                    jsx: true,
                },

                tsconfigRootDir: __dirname,
                project: ['tsconfig.json'],
            },
        },

        settings: {
            react: {
                version: 'detect',
            },
        },

        rules: {
            'array-callback-return': 'error',
            'block-scoped-var': 'error',
            'consistent-return': 'error',
            curly: 'error',
            'default-param-last': 'error',
            'dot-notation': 'error',
            eqeqeq: 'error',
            'no-alert': 'error',
            'no-console': 'error',
            'no-else-return': 'error',
            'no-empty-function': 'error',
            'no-eval': 'error',
            'no-extra-bind': 'error',
            'no-implicit-globals': 'error',
            'no-new': 'error',
            'no-new-func': 'error',
            'no-new-wrappers': 'error',
            'no-param-reassign': 'error',
            'no-return-assign': 'error',
            'no-sequences': 'error',
            'no-throw-literal': 'error',
            'no-undef': 'off',
            'no-unreachable-loop': 'error',
            'no-unsafe-optional-chaining': 'error',
            'no-unused-expressions': 'error',
            'no-useless-concat': 'error',
            'no-useless-return': 'error',
            'padded-blocks': 'off',
            'react/jsx-props-no-spreading': 'off',
            'react/prop-types': 'off',
            'react/react-in-jsx-scope': 'off',
            'react/require-default-props': 'off',
            'require-atomic-updates': 'error',
            '@typescript-eslint/no-explicit-any': 'off',
            'unused-imports/no-unused-imports': 'error',
            'vars-on-top': 'error',
            yoda: 'error',
            '@typescript-eslint/strict-boolean-expressions': 'off',
            '@typescript-eslint/no-non-null-assertion': 'off',
            '@typescript-eslint/no-unnecessary-condition': 'error',

            'no-restricted-imports': [
                'error',
                {
                    name: 'tailwind-merge',
                    importNames: ['twMerge'],
                    message: 'Please use twMergeCustom from utils/twMerge instead.',
                },
                {
                    name: 'react',
                    importNames: ['FC'],
                    message: 'Please remove this import and use global FC interface',
                },
                {
                    name: 'next/link',
                    message: 'Please use ExtendedNextLink instead',
                },
                {
                    name: 'urql',
                    importNames: ['createClient'],
                    message: 'Please use the custom createClient function from storefront/urql/fetcher.ts',
                },
                {
                    name: 'next-urql',
                    importNames: ['initUrqlClient'],
                    message: 'Please use the custom createClient function from storefront/urql/fetcher.ts',
                },
            ],

            'react-hooks/rules-of-hooks': 'error',

            'react/no-unknown-property': [
                'error',
                {
                    ignore: ['jsx', 'global', 'tid', 'data-tid'],
                },
            ],

            'react/jsx-curly-brace-presence': [
                'error',
                {
                    props: 'never',
                    children: 'never',
                    propElementValues: 'always',
                },
            ],

            'react/jsx-boolean-value': 'error',

            'react/jsx-no-useless-fragment': [
                'error',
                {
                    allowExpressions: true,
                },
            ],

            'react/self-closing-comp': 'error',

            'react/jsx-sort-props': [
                'error',
                {
                    callbacksLast: true,
                    shorthandFirst: true,
                    multiline: 'last',
                    reservedFirst: ['key'],
                },
            ],

            'no-relative-import-paths/no-relative-import-paths': [
                'error',
                {
                    allowSameFolder: true,
                },
            ],

            // Invalid HTML structure
            'no-restricted-syntax': [
                'error',
                {
                    selector:
                        'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="div"]',
                    message: 'Button elements cannot contain div elements. Use span or other phrasing content instead.',
                },
                {
                    selector: 'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="p"]',
                    message:
                        'Button elements cannot contain paragraph elements. Use span or other phrasing content instead.',
                },
                {
                    selector: 'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="h1"]',
                    message:
                        'Button elements cannot contain heading elements. Use span or other phrasing content instead.',
                },
                {
                    selector: 'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="h2"]',
                    message:
                        'Button elements cannot contain heading elements. Use span or other phrasing content instead.',
                },
                {
                    selector: 'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="h3"]',
                    message:
                        'Button elements cannot contain heading elements. Use span or other phrasing content instead.',
                },
                {
                    selector: 'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="h4"]',
                    message:
                        'Button elements cannot contain heading elements. Use span or other phrasing content instead.',
                },
                {
                    selector: 'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="h5"]',
                    message:
                        'Button elements cannot contain heading elements. Use span or other phrasing content instead.',
                },
                {
                    selector: 'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="h6"]',
                    message:
                        'Button elements cannot contain heading elements. Use span or other phrasing content instead.',
                },
                {
                    selector: 'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="ul"]',
                    message:
                        'Button elements cannot contain list elements. Use span or other phrasing content instead.',
                },
                {
                    selector: 'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="ol"]',
                    message:
                        'Button elements cannot contain list elements. Use span or other phrasing content instead.',
                },
                {
                    selector:
                        'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="section"]',
                    message:
                        'Button elements cannot contain section elements. Use span or other phrasing content instead.',
                },
                {
                    selector:
                        'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="article"]',
                    message:
                        'Button elements cannot contain article elements. Use span or other phrasing content instead.',
                },
                {
                    selector: 'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="a"]',
                    message:
                        'Button elements cannot contain anchor elements. Interactive elements cannot be nested inside other interactive elements.',
                },
                {
                    selector: 'JSXElement[openingElement.name.name="a"] JSXElement[openingElement.name.name="button"]',
                    message:
                        'Anchor elements cannot contain button elements. Interactive elements cannot be nested inside other interactive elements.',
                },
                {
                    selector:
                        'JSXElement[openingElement.name.name="button"] JSXElement[openingElement.name.name="button"]',
                    message:
                        'Button elements cannot contain other button elements. Interactive elements cannot be nested.',
                },
            ],

            // Additional jsx-a11y rules for better HTML validation
            'jsx-a11y/no-noninteractive-element-interactions': [
                'error',
                {
                    handlers: ['onClick', 'onMouseDown', 'onMouseUp', 'onKeyPress', 'onKeyDown', 'onKeyUp'],
                },
            ],
            'jsx-a11y/anchor-is-valid': [
                'error',
                {
                    components: ['Link', 'ExtendedNextLink'],
                    specialLink: ['hrefLeft', 'hrefRight'],
                    aspects: ['invalidHref', 'preferButton'],
                },
            ],
        },
    },
];
