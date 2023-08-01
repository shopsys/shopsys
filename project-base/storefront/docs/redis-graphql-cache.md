## Redis GraphQL cache

- this cache is used for selected queries and is intended for server side only
- the reason is to improve server side performance of the Storefront

### How does it work

- the cache is set via the graphql directive `@_redisCache` which accepts TTL in seconds
- the custom URQl fetcher tries to read the data from the cache, if it does not find it, it calls the API
- the cache can be deactivated (e.g. for development purposes) by setting `GRAPHQL_REDIS_CACHE=0` in your `.env.local` file

### How to use it

- to apply cache to some query, simply set the `@_redisCache` directive on the query

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
