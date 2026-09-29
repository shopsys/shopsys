# GraphQL

On the Storefront, we use and consume the backend GraphQL API. We don't use raw GraphQL, but we write our queries and mutations and then generate the hooks and types with a library (`graphql-code-generator`) .

Under the hood of `graphql-code-generator` , the `urql` GraphQL client is used. We also use URQL for other chores, such as for caching the GraphQL layer (see the [docs](./caching/graphcache.md) for more info about our caching logic).

## Structure

- **docs** - generated (library `graphql-markdown`) markdown documentation from GraphQL schema
- **types.ts** - generated GQL types used on Storefront
- **requests** - here you can find editable files, such as all `queries`, `mutations`, and `fragments`, based on which the hooks and types are generated, and files with a `.generated.` suffix, which are generated based on the `.graphql` files.

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

## Generated types and fragments

Only edit `.graphql` source files in `graphql/requests`. Files with a `.generated.` suffix and `graphql/types.ts` are generated artifacts; recreate them with `make generate-schema` after changing an operation or fragment.

Use a generated operation type for data that belongs to one operation and is passed through its local component tree. It keeps a component contract tied to exactly the fields the operation fetches:

```ts
import type { TypeSearchProductsQuery } from 'graphql/requests/search/queries/SearchProductsQuery.generated';

type SearchProductsContentProps = {
    productsSearch: TypeSearchProductsQuery['productsSearch'];
};
```

Prefer inference inside an operation's component tree. `mapConnectionEdges` derives the node type from the actual input and skips missing edges and nodes. There is no need to supply a fragment type or cast the result:

```ts
const products = mapConnectionEdges(productsSearch.edges);
```

When components need a named data contract, prefer the existing generated fragment type instead of removing the fragment and recreating its name through an operation-derived alias:

```ts
import type { TypeListedOrderFragment } from 'graphql/requests/orders/fragments/ListedOrderFragment.generated';

type OrderProps = {
    order: TypeListedOrderFragment;
};
```

Operation-derived aliases are available when they genuinely simplify local code, but are not a goal of fragment cleanup. Use `NonNullable` only where the caller actually checks for a missing value. Do not use schema object types from `graphql/types.ts` as query result types: those describe all available fields, including ones the operation did not select.

### Choosing fragment boundaries

Write an unshared selection directly in its query, mutation or owning fragment when doing so simplifies the whole feature without introducing a replacement type layer. `NotificationBarsQuery.graphql` is a small example: all bar fields are visible in the query, shared image fields still use `ImageFragment`, and consumers do not need a new alias.

Use a fragment for an intentionally shared GraphQL selection. `CartFragment` is returned by queries and mutations; `ListedProductFragment` supplies the same product-card data in several contexts. Sharing can also occur between branches of one operation. Do not combine two selections merely because their fields happen to match today: they should represent a contract intended to evolve together.

Fragments have a second useful role: providing named generated types for components and helpers. `ListedOrderFragment`, the navigation fragments and `TransportWithAvailablePaymentsFragment` serve this purpose even when they have only one direct GraphQL spread. Keep these contracts rather than replacing straightforward imports with handwritten aliases and nested `NonNullable` or `Extract` expressions. Count both GraphQL reuse and type consumers when assessing a fragment.

A single-use fragment can also improve readability when a substantial, cohesive selection makes its parent easier to understand. For example, `CartModificationsFragment` groups cart-change messages and `TransportWithAvailablePaymentsAndStoresFragment` groups transport, payment and pickup data inside the large shared cart selection.

Small fragments used only inside a shared parent and without their own type consumers can be inlined there: `ProductFilterOptionsFragment` contains its brands, flags and parameter branches. Conversely, `ProductReviewFragment` remains a named generated contract for review components. Reusing a parent does not by itself require or rule out a fragment for each child.

Prefer inference where no explicit contract is needed, such as mapping connection edges. Preserve useful named generated types elsewhere. Judge the whole change by readability and maintenance effort, not the number of deleted fragments.

Preserve `__typename`, field aliases, arguments, directives and type conditions when inlining. For example, article links keep their `... on ArticleLink` condition, and parameter filters retain their checkbox, color and slider inline fragments. `__typename` is needed by application logic and URQL cache reads; see [best practices](./best-practises.md).

### Shared items, operation-specific lists

Share a cohesive item contract without forcing every consumer to fetch the same surrounding list metadata. Autocomplete selects only `totalCount` and `edges` (with their `__typename` fields), while full search additionally needs filters, ordering and pagination. `SearchQuery` and `SearchProductsQuery` share these full-search requirements through `SearchResultsConnectionFragment`; autocomplete does not use it. Keep different requirements in their owning operations instead of adding optional fields to a handwritten response type.

Before splitting an item fragment, check all consumers, including analytics, cache updates and conditional UI. A field hidden by a compact card can still be required by its analytics event. Extract a smaller shared contract only when the consumers genuinely need different data and the saving justifies the additional component and type boundaries. Avoid both a universal fragment that accumulates unrelated requirements and tiny fragments for every group of fields.

Removing a requested field can save more than response bytes when its resolver performs work lazily. Autocomplete no longer requests `productFilterOptions`, so the standard product connection does not invoke its filter-options closure. This does not eliminate product retrieval or result counting, and other search providers may compute facets as part of their search request. Measure before claiming a latency improvement.

### Separate display, actions and page details

`CompactProductFragment` is the shared display and analytics contract for autocomplete, favorites and last-visited cards. `ListedProductFragment` spreads it and adds the stock, unit and pickup fields used by full shopping cards. Shared display fields are maintained once. Both variants accept their generated fragment types; compact cards do not initialize cart or wishlist/comparison action hooks. Select the card with `cardVariant="compact"`, independently of the slider's layout `variant`. Compact configuration cannot enable purchase controls without the full data contract.

Reuse already-loaded data before introducing another request: product JSON-LD reads the initial five newest reviews from product detail; the review query remains available for pagination and sorting. Similarly, cart-change messages use a small named item contract while live cart items and cart mutation responses retain their complete state. A mutation that only invalidates a list can return identity, but that is not a general rule for all mutations.

### Connectors and verification

Keep adapters that perform real transformations, such as normalizing nullable customer fields for forms or flattening telephone data. A generated API response and a form model have different responsibilities. Do not add a connector or duplicate response interface merely to rename a generated type.

After a refactor, regenerate with `make generate-schema`, check TypeScript and verify affected behavior. Inlining a selection does not by itself reduce response data, improve type safety or prove a performance improvement. Its benefit is making an operation's fields easier to find. Generated fragment types are also type-safe; the separate `mapConnectionEdges` inference improvement avoids claiming fields absent from the actual input.
