import type { CodegenConfig } from '@graphql-codegen/cli';

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
        './graphql/generated/index.tsx': {
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
            plugins: ['typescript', 'typescript-operations', 'fragment-matcher', 'typescript-urql'],
        },
    },
};
export default config;
