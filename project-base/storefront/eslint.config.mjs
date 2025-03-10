import react from "eslint-plugin-react";
import unusedImports from "eslint-plugin-unused-imports";
import typescriptEslint from "@typescript-eslint/eslint-plugin";
import reactHooks from "eslint-plugin-react-hooks";
import noRelativeImportPaths from "eslint-plugin-no-relative-import-paths";
import { fixupPluginRules } from "@eslint/compat";
import globals from "globals";
import tsParser from "@typescript-eslint/parser";
import path from "node:path";
import { fileURLToPath } from "node:url";
import js from "@eslint/js";
import { FlatCompat } from "@eslint/eslintrc";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const compat = new FlatCompat({
    baseDirectory: __dirname,
    recommendedConfig: js.configs.recommended,
    allConfig: js.configs.all
});

export default [{
    ignores: [
        "node_modules/*",
        "cypress/*",
        ".next/*",
        "public/*",
        "!**/.prettierrc.js",
        "graphql/types.ts",
        "**/*.generated.*",
        "config/*",
        "**/eslint.config.mjs",
        "**/schema.graphql.json",
        "**/schema-compressed.graphql.json",
        "**/pnpm-lock.json",
        "**/package.json",
        "**/tsconfig.json",
        ".pnpm-store/*",
        "**/eslint-rules",
    ],
}, ...compat.extends(
    "eslint:recommended",
    "plugin:react/recommended",
    "plugin:@typescript-eslint/eslint-recommended",
    "plugin:@typescript-eslint/recommended",
    "prettier",
), {
    plugins: {
        react,
        "unused-imports": unusedImports,
        "@typescript-eslint": typescriptEslint,
        "react-hooks": fixupPluginRules(reactHooks),
        "no-relative-import-paths": noRelativeImportPaths,
    },

    languageOptions: {
        globals: {
            ...globals.browser,
        },

        parser: tsParser,
        ecmaVersion: 12,
        sourceType: "module",

        parserOptions: {
            ecmaFeatures: {
                jsx: true,
            },

            tsconfigRootDir: __dirname,
            project: ["tsconfig.json"],
        },
    },

    settings: {
        react: {
            version: "detect",
        },
    },

    rules: {
        "array-callback-return": "error",
        "block-scoped-var": "error",
        "consistent-return": "error",
        curly: "error",
        "default-param-last": "error",
        "dot-notation": "error",
        eqeqeq: "error",
        "no-alert": "error",
        "no-console": "error",
        "no-else-return": "error",
        "no-empty-function": "error",
        "no-eval": "error",
        "no-extra-bind": "error",
        "no-implicit-globals": "error",
        "no-new": "error",
        "no-new-func": "error",
        "no-new-wrappers": "error",
        "no-param-reassign": "error",
        "no-return-assign": "error",
        "no-sequences": "error",
        "no-throw-literal": "error",
        "no-undef": "off",
        "no-unreachable-loop": "error",
        "no-unsafe-optional-chaining": "error",
        "no-unused-expressions": "error",
        "no-useless-concat": "error",
        "no-useless-return": "error",
        "padded-blocks": "off",
        "react/jsx-props-no-spreading": "off",
        "react/prop-types": "off",
        "react/react-in-jsx-scope": "off",
        "react/require-default-props": "off",
        "require-atomic-updates": "error",
        "@typescript-eslint/no-explicit-any": "off",
        "unused-imports/no-unused-imports": "error",
        "vars-on-top": "error",
        yoda: "error",
        "@typescript-eslint/strict-boolean-expressions": "off",
        "@typescript-eslint/no-non-null-assertion": "off",
        "@typescript-eslint/no-unnecessary-condition": "error",

        "no-restricted-imports": ["error", {
            name: "tailwind-merge",
            importNames: ["twMerge"],
            message: "Please use twMergeCustom from utils/twMerge instead.",
        }, {
            name: "react",
            importNames: ["FC"],
            message: "Please remove this import and use global FC interface",
        }, {
            name: "next/link",
            message: "Please use ExtendedNextLink instead",
        }, {
            name: "urql",
            importNames: ["createClient"],
            message: "Please use the custom createClient function from storefront/urql/fetcher.ts",
        }, {
            name: "next-urql",
            importNames: ["initUrqlClient"],
            message: "Please use the custom createClient function from storefront/urql/fetcher.ts",
        }],

        "react-hooks/rules-of-hooks": "error",

        "react/no-unknown-property": ["error", {
            ignore: ["jsx", "global", "tid"],
        }],

        "react/jsx-curly-brace-presence": ["error", {
            props: "never",
            children: "never",
            propElementValues: "always",
        }],

        "react/jsx-boolean-value": "error",

        "react/jsx-no-useless-fragment": ["error", {
            allowExpressions: true,
        }],

        "react/self-closing-comp": "error",

        "react/jsx-sort-props": ["error", {
            callbacksLast: true,
            shorthandFirst: true,
            multiline: "last",
            reservedFirst: ["key"],
        }],

        "no-relative-import-paths/no-relative-import-paths": ["error", {
            allowSameFolder: true,
        }],
    },
}];