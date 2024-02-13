# URQL Graphcache Exchange

## Introduction

The URQL library provides a flexible and efficient way to interact with GraphQL APIs in your JavaScript or TypeScript application. A significant part of this interaction is dealing with caching to reduce network requests, improve responsiveness, and manage the local state. This is where the Graphcache exchange comes in. When using this module, ensure you're familiar with the URQL graph cache and its related concepts. It's also crucial to be aware of the GraphQL schema and the generated GraphQL types, as they play a vital role in how the cache is managed.

### What is Graphcache?

Graphcache is an advanced, normalized caching utility that comes as an exchange for the URQL library. Exchanges in URQL are modular pieces of logic that can be added to the core functionality, and Graphcache is one such exchange explicitly designed for caching.

While URQL comes with basic caching out of the box, Graphcache offers:

-   **Normalized caching**: This means data is stored as a flat table of records, similar to how databases store data. This ensures no duplicate data and provides efficient updates and look-ups.
-   **Automatic updates**: With knowledge of your schema and types, Graphcache can automatically update your cache when you perform mutations or receive new data.
-   **Offline support**: Graphcache supports persisting your cache and resuming operations that were made while offline.

### How is it used in this code?

This code defines how the cache should behave based on the application's requirements. Specifically, it:

-   Specifies the **schema** to help the cache understand the GraphQL schema and provide automatic updates.
-   Defines **keys** to uniquely identify each type in the cache.
-   Details how cache **updates** should occur after certain mutations.
-   Uses **optimistic updates** to immediately reflect changes in the UI before a server's response is received, providing a smoother user experience.

### Official Documentation

For a comprehensive understanding and in-depth details on Graphcache, its benefits, and its API, please refer to the [official URQL documentation](https://formidable.com/open-source/urql/docs/graphcache/). Some of the things, however, are not explained in the best way and might seem chaotic. Especially when it comes to manual updates. For tips on how to manually update the cache after mutations, read the part of this documentation dedicated to it.

## Keys

-   `keyNull`, `keyUuid`, `keyName`, `keyCode`,: These are keying functions used to generate keys for various GraphQL types in the cache. They are based on properties like `uuid`, `name`, `code`, etc.

-   Keys serve as unique identifiers for each type of data (or record) in the cache. Given that Graphcache utilizes a normalized cache structure, these are used to combine different fragments of the same entity type. You can read more about this in the [official docs](https://formidable.com/open-source/urql/docs/graphcache/normalized-caching/#custom-keys-and-non-keyable-entities).

## Cache Configuration

The cache is configured with:

1. **Schema**: The GraphQL introspection schema.
2. **Keys**: This is a mapping between GraphQL types and their keying functions.
3. **Updates** (located in `updates.ts`): This provides update logic after mutations. It determines how the cache should be invalidated or updated after specific mutations like `Login`, `Logout`, `AddToCart`, etc.
4. **Optimistic** (located in `optimistic.ts`): The module provides optimistic updates for some mutations like `ChangeTransportInCart` and `ChangePaymentInCart`. This means even before the mutation result comes back from the server, the cache is updated in an optimistic manner based on certain assumptions.

## Utility Functions (located in `helpers.ts`)

-   `invalidateFields`: Given a cache instance and a list of fields, this function invalidates these fields in the cache. This can be useful after certain operations, like a user login or logout, where you might want to invalidate cached data to fetch fresh data from the server.

## Manual Cache Updates (located in `updates.ts`)

### Examples

-   `manuallyUpdateCartQuery`: Manually updates the cart query in the cache.
-   `manuallyRemoveProductListQuery` & `manuallyUpdateProductListQuery`: Updaters for product lists and related queries.

### Cookbook & Tips:

If you want to implement a manual cache update, you have to follow a set of rules. The benefits of manual cache updates are usually paid for by trial and error, and debugging. The following rules and steps should help you limit that.

#### Name the manual update after the actual mutation

You need to name the property inside `cacheUpdates` after the actual mutation. So if you have a `MyMutaion.graphql` file like this:

```gql
mutation MyMutationApplicationName() {
    MutationActualName(){
        ...
    }
}
```

you have to add the key seen below to `cacheUpdates`:

```ts
export const cacheUpdates: UpdatesConfig = {
    Mutation: {
        MutationActualName(result: MyMutationApplicationNameApi, args: MyMutationApplicationNameVariablesApi, cache) {
            ...
        }
    },
};
```

#### Type your result and arguments correctly

The next step is to add correct TypeScript types for `result` and `args`. Mind that while the key (function name), which we have added in the step above, is the actual name of the mutation (how it is defined on API), the types used for `result` and `args` are based on the application name (the named used to name the mutation in the `.gql` file). The type of the `result` is then the application name of the mutation suffixed by the word `Api`, and the `args` have a suffix `VariablesApi`. 

There are also types defined for the API input object, which are usually not the same you define by hand for your queries and they usually do not match. To give an example, the `MutationAddToCartArgsApi` type is generated by default and should not be used, as `AddToCartMutationVariablesApi` is the correct one defined in the code. If you are unsure, you can always log the actual contents in the console and make sure they are of the type you want them to be.

#### Update the query

The last and most error-prone step is to actually update the query. You can do so by calling the `cache.updateQuery` method. However, you have to keep in mind, that if you do it incorrectly, for example by using wrong variables or query document, there will be no error reported, so you will not know it does not work.

Because of that, it is better to implement the updates incrementally. First, try reading the cache. So, to follow with the example from before, Imagine you want to update a query called `MyQuery`. You have already correctly typed the `result` and `args` parameters, and now you want to read the cache as the first step.

```ts
export const cacheUpdates: UpdatesConfig = {
    Mutation: {
        MutationActualName(result: MyMutationApplicationNameApi, args: MyMutationApplicationNameVariablesApi, cache: Cache) {
            // add the query document and map the variables correctly
            cache.readQuery({ query: MyQueryDocumentApi, variables: { myVariable: args.mutationVariable } });
        },
    },
};
```

If the result of the query is in the cache already (for example by fetching the query before), reading it should give you the object currently stored in the cache. If you have fetched the query before and reading it produces `undefined`, you are doing something wrong. Read the tips below in that case.

If you are able to read the cache, change `readQuery` to `updateQuery`. That function takes an extra argument; the updater function. It is the last thing you have to do.

```ts
export const cacheUpdates: UpdatesConfig = {
    Mutation: {
        MutationActualName(result: MutationResultType, args: MutationVariablesType, cache: Cache) {
            cache.readQuery(
                { query: MyQueryDocumentApi, variables: { myVariable: args.mutationVariable } },
                // add the updater function
                (data) => ({ ...data, updatedProperty: result.mutationResult }),
            );
        },
    },
};
```

The provided `data` object is the current value in the cache (equal to what you would get by reading the cache). You can analyze it to see what you need to provide as a return for the updater. Make sure to return an object compatible with what the query can return. Also make sure to include `__typename` for the result. With this, your update should be ready.

#### Cannot read the query from cache

If you cannot read from cache even though you have fetched the query before, it is probably because you are reading the cache wrong. In such scenario, you can incrementally add levels to how you read from it. First, loop through all the cached queries

```ts
cache.inspectFields('Query').forEach((fieldInfo) => {
    // check what queries you have cached
});
```

After checking that, you can now try reading a specific query. Again, make sure that `myQuery` mirrors the actual name of your query, same as with the mutation before. The benefit of using this approach is that you use the arguments from the cache, which makes sure that you are passing the correct arguments.

```ts
cache.inspectFields('Query').forEach((fieldInfo) => {
    if (fieldInfo.fieldName === 'myQuery') {
        cache.readQuery({ query: MyQueryDocumentApi, variables: fieldInfo.arguments });
    }
});
```

If you can read from the cache this way, you can just mimic this behavior by directly reading and not looping through all queries.

## Optimistic Cache Updates (located in `omptimistic.ts`)

### Examples

-   `getOptimisticChangeTransportInCartResult` & `getOptimisticChangePaymentInCartResult`: These functions provide optimistic results for the respective mutations.
-   `getPaymentFromTransport`: Helper function to get a specific payment method from a transport based on its UUID.
