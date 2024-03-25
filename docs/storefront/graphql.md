# GraphQL

On the Storefront, we use and consume the backend GraphQL API. We don't use raw GraphQL, but we write our queries and mutations and then generate the hooks and types with a library (`graphql-code-generator`) .

Under the hood of `graphql-code-generator` , the `urql` GraphQL client is used. We also use URQL for other chores, such as for caching the GraphQL layer (see the [docs](./caching.md) for more info about our caching logic).

## Structure

-   **docs** - generated (library `graphql-markdown`) markdown documentation from GraphQL schema
-   **types.ts** - generated GQL types used on Storefront
-   **requests** - here you can find editable files, such as all `queries`, `mutations`, and `fragments`, based on which the hooks and types are generated, and files with a `.generated.` suffix, which are generated based on the `.graphql` files.

## Generate hooks and types

In order to run `graphql-code-generator` and let it generate hooks and types for the Storefront, you first need to copy the GraphQL schema from `project-base/app/schema.graphql` to the `project-base/storefront/schema.graphql`, then you are able to run the command below.

```bash
pnpm gql
```

Then you need to remove `schema.graphql` file from `project-base/storefront`.

You can see a possibility for automation, and you are right. All of this can be done by one simple command, which has to be executed from the root folder.

```bash
make generate-schema
```

After this, you should see your hooks generated near your operation files, and the global types file generated in as `/graphql/types.ts`.
