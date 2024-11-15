import type { CodegenConfig } from '@graphql-codegen/cli';
import { NearOperationFileConfig } from '@graphql-codegen/near-operation-file-preset';

const codegenTypescriptConfig = {
    typesPrefix: 'Type',
    withHOC: false,
    withComponent: false,
    scalars: {
        Money: 'string',
        Uuid: 'string',
    },
    avoidOptionals: true,
    omitOperationSuffix: true,
    importTypes: true,
};

const config: CodegenConfig = {
    overwrite: true,
    schema: 'schema.graphql',
    documents: './graphql/requests/**/*.graphql',
    generates: {
        // 1. schema
        'schema.graphql.json': {
            plugins: ['introspection'],
            config: {
                minify: true,
            },
        },

        // 2. types
        './graphql/types.ts': {
            config: codegenTypescriptConfig,
            plugins: ['typescript'],
        },

        // 3. generated
        './graphql': {
            preset: 'near-operation-file',
            presetConfig: {
                baseTypesPath: 'types',
                extension: '.generated.tsx',
            } as NearOperationFileConfig,
            config: {
                ...codegenTypescriptConfig,
                withHooks: true,
            },
            plugins: ['typescript-operations', 'fragment-matcher', 'typescript-urql'],
        },

        // 4. ssr
        './graphql/requests': {
            preset: 'near-operation-file',
            presetConfig: {
                baseTypesPath: '../types',
                extension: '.ssr.tsx',
            } as NearOperationFileConfig,
            config: {
                ...codegenTypescriptConfig,
                withHooks: false,
            },
            plugins: ['typescript-operations', 'fragment-matcher', 'typescript-urql'],
        },
    },
};

export default config;
