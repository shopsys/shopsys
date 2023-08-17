import type { CodegenConfig } from '@graphql-codegen/cli';

const config: CodegenConfig = {
    overwrite: true,
    schema: 'schema.graphql',
    // generates: {
    //     'schema.graphql.json': {
    //         plugins: ['introspection'],
    //         config: {
    //             minify: true,
    //         },
    //     },
    documents: './graphql/requests/**/*.graphql',
    generates: {
        './graphql/requests/types.ts': {
            plugins: ['typescript', 'fragment-matcher'],
            config: {
                typesSuffix: 'Api',
                // withHooks: true,
                // withHOC: false,
                // withComponent: false,
                // scalars: {
                //     Money: 'string',
                //     Uuid: 'string',
                // },
                avoidOptionals: true,
                // omitOperationSuffix: true,
            },
        },
        './graphql/requests/': {
            preset: 'near-operation-file',
            presetConfig: { extension: '.generated.tsx', baseTypesPath: 'types.ts' },
            plugins: ['typescript-operations', 'typescript-urql', 'fragment-matcher'],
            config: {
                typesSuffix: 'Api',
                withHooks: true,
                withHOC: false,
                withComponent: false,
                scalars: {
                    Money: 'string',
                    Uuid: 'string',
                },
                avoidOptionals: true,
                omitOperationSuffix: true,
            },
        },
    },
};
export default config;
