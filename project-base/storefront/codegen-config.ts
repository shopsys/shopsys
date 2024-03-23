import type { CodegenConfig } from '@graphql-codegen/cli';
import { NearOperationFileConfig } from '@graphql-codegen/near-operation-file-preset';

const codegenTypescriptConfig = {
    withHooks: true,
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
        'schema.graphql.json': {
            plugins: ['introspection'],
            config: {
                minify: true,
            },
        },
        './graphql/types.ts': {
            config: codegenTypescriptConfig,
            plugins: ['typescript'],
        },
        './graphql/': {
            preset: 'near-operation-file',
            presetConfig: {
                baseTypesPath: 'types',
                extension: '.generated.tsx',
            } as NearOperationFileConfig,
            config: codegenTypescriptConfig,
            plugins: ['typescript-operations', 'fragment-matcher', 'typescript-urql'],
        },
    },
};

export default config;
