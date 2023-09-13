# Caching

We use several caching layers. Each of them serves a different purpose. Whereas the URQL cache exchange is present in the client's browser, Redis is present on the server and is shared among all users.

## 1. URQL cache exchange

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
