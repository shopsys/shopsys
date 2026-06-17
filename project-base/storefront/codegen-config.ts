import type { CodegenConfig } from '@graphql-codegen/cli';
import { NearOperationFileConfig } from '@graphql-codegen/near-operation-file-preset';

const codegenTypescriptConfig = {
    typesPrefix: 'Type',
    withHooks: true,
    withHOC: false,
    withComponent: false,
    scalars: {
        DateTime: 'string',
        FileUpload: 'File',
        Money: 'string',
        Password: 'string',
        Uuid: 'string',
    },
    avoidOptionals: {
        field: true,
        object: true,
        inputValue: false,
        defaultValue: true,
    },
    omitOperationSuffix: true,
    importTypes: true,
    preResolveTypes: false,
    skipTypename: false,
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
            plugins: [{ add: { content: '// @ts-nocheck' } }, 'typescript-operations', 'typescript-urql'],
        },
    },
};

export default config;
