# GraphQL

On the Storefront side we use and consume the backend GraphQL API. We don't use raw GraphQL but we write our queries and mutations and then generate with library `graphql-code-generator` hooks and types for its usage in Storefront.

Under the hood of `graphql-code-generator` is used `urql` GraphQL client. Which we use also for caching the frontend requests, see the [docs](./caching.md) for more info about our caching logic.

## Structure

-   **docs** - generated (library `graphql-markdown`) markdown documentation from GraphQL schema
-   **generated** - generated hooks and types used in Storefront application
-   **requests** - only editable files can be found here, place for all `queries` and `mutations` (and `fragments`) which are generated hooks and types generated from

## Generate hooks and types

In order to run `graphql-code-generator` and let it generate hooks and types for our Storefront you first need to copy GraphQL schema from `project-base/app/schema.graphql` to the `project-base/storefront/schema.graphql` then you are able to run generate

```bash
pnpm gql
```

Then you need to remove the `schema.graphql` file from `project-base/storefront`.

In this phase you can see possibilities for automation and you are right. All of this can be done by one simple command which is being executed from root folder

```bash
make generate-schema
```

After this you should see your hooks and types being generated in `project-base/storefront/graphql/generated/index.tsx`.
