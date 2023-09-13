# Caching

We use several caching layers. Each of them serves a different purpose. Whereas the URQL cache exchange is present in the client's browser, Redis is present on the server and is shared among all users.

## URQL Graphcache Exchange

### Introduction

The URQL library provides a flexible and efficient way to interact with GraphQL APIs in your JavaScript or TypeScript application. A significant part of this interaction is dealing with caching to reduce network requests, improve responsiveness, and manage local state. This is where the Graphcache exchange comes in.

#### What is Graphcache?

Graphcache is an advanced, normalized caching utility that comes as an exchange for the URQL library. Exchanges in URQL are modular pieces of logic that can be added to the core functionality, and Graphcache is one such exchange designed specifically for caching.

While URQL comes with basic caching out of the box, Graphcache offers:

- **Normalized caching**: This means data is stored as a flat table of records, similar to how databases store data. This ensures no duplicate data and provides efficient updates and look-ups.
- **Automatic updates**: With knowledge of your schema and types, Graphcache can automatically update your cache when you perform mutations or receive new data.
- **Offline support**: Graphcache supports persisting your cache and resuming operations that were made while offline.

#### How is it used in this code?

This code defines how the cache should behave based on the application's requirements. Specifically, it:

- Specifies the **schema** to help the cache understand the GraphQL schema and provide automatic updates.
- Defines **keys** to uniquely identify each type in the cache.
- Details how cache **updates** should occur after certain mutations.
- Uses **optimistic updates** to immediately reflect changes in the UI before a server's response is received, providing a smoother user experience.

#### Official Documentation

For a comprehensive understanding and in-depth details on Graphcache, its benefits, and its API, please refer to the [official URQL documentation](https://formidable.com/open-source/urql/docs/graphcache/).

### Keys

- `keyNull`, `keyWishlist`, `keyUuid`, `keyName`, `keyCode`, `keyUrl`, `keyComparison`: These are keying functions used to generate keys for various GraphQL types in the cache. They are based on properties like `uuid`, `name`, `code`, `url`, etc.

- Keys serve as unique identifiers for each type of data (or record) in the cache. Given that Graphcache utilizes a normalized cache structure, these are used to combine different fragments of the same entity type. You can read more about this in the [official docs](https://formidable.com/open-source/urql/docs/graphcache/normalized-caching/#custom-keys-and-non-keyable-entities).

### Cache Configuration

The cache is configured with:

1. **Schema**: The GraphQL introspection schema.
2. **Keys**: This is a mapping between GraphQL types and their keying functions.
3. **Updates**: This provides update logic after mutations. It determines how the cache should be invalidated or updated after specific mutations like `Login`, `Logout`, `AddToCart`, etc.
4. **Optimistic**: For some mutations like `ChangeTransportInCart` and `ChangePaymentInCart`, the module provides optimistic updates. This means even before the mutation result comes back from the server, the cache is updated in an optimistic manner based on certain assumptions.

### Utility Functions

- `invalidateFields`: Given a cache instance and a list of fields, this function invalidates these fields in the cache. This can be useful after certain operations, like a user login or logout, where you might want to invalidate cached data to fetch fresh data from the server.
  
- `manuallyUpdateCartFragment`: Manually updates the cart fragment in the cache.

- `clearComparisonQueryFragment` & `clearWishlistQueryFragment`: Clear the comparison or wishlist fragment from the cache.

- `getOptimisticChangeTransportInCartResult` & `getOptimisticChangePaymentInCartResult`: These functions provide the optimistic results for the respective mutations.

- `getPaymentFromTransport`: Helper function to get a specific payment method from a transport based on its UUID.

When using this module, ensure you're familiar with the URQL graph cache and its related concepts. It's also crucial to be aware of the GraphQL schema and the generated GraphQL types, as they play a vital role in how the cache is managed.

## 2. Redis GraphQL cache

This cache is used for selected queries and is intended for server-side only. The goal is to improve server-side performance of the Storefront.

### How does it work

The cache is set via a graphql directive `@_redisCache` which accepts TTL in seconds.

The custom URQL fetcher tries to read the data from the cache, if it does not find it, it calls the API.

The cache can be deactivated (e.g. for development purposes) by setting `GRAPHQL_REDIS_CACHE=0` in your `.env.local` file.

### How to use it

To apply cache to a query, simply set the `@_redisCache` directive on it.

#### Example

query is not cached

```graphql
query NavigationQuery {
  navigation {
    name
    link
    ...CategoriesByColumnFragment
  }
}
```

query is cached for 1 hour

```graphql
query NavigationQuery @_redisCache(ttl: 3600) {
  navigation {
    name
    link
    ...CategoriesByColumnFragment
  }
}
```
