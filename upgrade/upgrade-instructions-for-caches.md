# Upgrade Instructions for caches

As the [`doctrine/cache`](https://github.com/doctrine/cache) library became deprecated,
it's necessary to change the implementation of caching to any PSR-6 (PSR-16) compatible cache.

We show here one of the possible solutions with the use of Symfony cache (https://symfony.com/doc/current/components/cache.html),
but the final choice is up to you.

## Current state

First, let's introduce a starting implementation.
It comes from already implemented caches in Shopsys Framework.

The cache uses Redis as storage and is defined in the services file along with the facade using the cache

```yaml
# app/config/services.yaml

services:
    shopsys.shop.product.bestselling_product.cache_provider:
        class: Doctrine\Common\Cache\RedisCache
        calls:
            - { method: setRedis, arguments: ['@snc_redis.bestselling_products'] }

    App\CachedBestsellingProductFacade:
        arguments:
            - '@shopsys.shop.product.bestselling_product.cache_provider'
```

And the definition of the storage

```yaml
# app/config/packages/snc_redis.yaml

bestselling_products:
    type: 'phpredis'
    alias: 'bestselling_products'
    dsn: 'redis://%redis_host%'
    options:
        prefix: '%env(REDIS_PREFIX)%%build-version%:cache:bestselling_products:'
```

Usage in the facade is following (shortened)

```php
use Doctrine\Common\Cache\CacheProvider;

class CachedBestsellingProductFacade
{
    protected const LIFETIME = 43200; // cache entries are invalidated after 12h

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    protected $cacheProvider;

    // ... properties

    /**
     * @param \Doctrine\Common\Cache\CacheProvider $cacheProvider
     */
    public function __construct(
        CacheProvider $cacheProvider,
        // ... dependencies
    )
    {
        $this->cacheProvider = $cacheProvider;
        // ... set properties
    }

    /**
     * @return int[]
     */
    public function getBestsellingProductIds(): array
    {
        $cacheId = 'bestselling-products';

        $productIds = $this->cacheProvider->fetch($cacheId);

        if ($productIds === false) {
            // $productIds = ... time-consuming operation to calculate bestselling products

            $this->cacheProvider->save($cacheId, $productIds, static::LIFETIME);
        }

        return $productIds;
    }
}
```

## Solution

At first, we need to define a new cache pool.  
The `lifetime` directive is the value previously set in the `LIFETIME` constant.  
The `provider` directive contains the same snc_redis service previously set as an argument for `setRedis` method.

```yaml
# config/packages/cache.yaml
framework:
    cache:
        pools:
            bestselling_product_cache:
                adapter: cache.adapter.redis
                provider: snc_redis.bestselling_products
                default_lifetime: 43200
```

And now for the change of the implementation of the facade using the cache.

The `LIFETIME` constant is not necessary anymore:

```diff
 use Doctrine\Common\Cache\CacheProvider;
 
 class CachedBestsellingProductFacade
 {
-    protected const LIFETIME = 43200; // cache entries are invalidated after 12h
```

The type of the passed cache service will change, so let's adapt the code:

```diff
  /**
-  * @var \Doctrine\Common\Cache\CacheProvider
+  * @var \Symfony\Contracts\Cache\CacheInterface
   */
- protected $cacheProvider;
+ protected CacheInterface $cache;
  
  // ... properties
  
  /**
-  * @param \Doctrine\Common\Cache\CacheProvider $cacheProvider
+  * @param \Symfony\Contracts\Cache\CacheInterface $cache
   */
  public function __construct(
-     CacheProvider $cacheProvider,
+     CacheInterface $cache,
      // ... dependencies
  )
  {
-     $this->cacheProvider = $cacheProvider;
+     $this->cache = $cache;
      // ... set properties
  }
```

And don't forget about the `getBestsellingProductIds` method.
The new implementation looks like this:

```php
/**
 * @return int[]
 */
public function getBestsellingProductIds(): array
{
    $cacheId = 'bestselling-products';

    return $this->cache->get(
        $cacheId,
        function () {
            // return ... time-consuming operation to calculate bestselling products
        }
    );
}
```

The last thing that remains is an update of the `services.yaml` file:

```diff
  # app/config/services.yaml
  
  services:
-     shopsys.shop.product.bestselling_product.cache_provider:
-         class: Doctrine\Common\Cache\RedisCache
-         calls:
-             - { method: setRedis, arguments: ['@snc_redis.bestselling_products'] }
  
      App\CachedBestsellingProductFacade:
          arguments:
-             - '@shopsys.shop.product.bestselling_product.cache_provider'
+             - '@bestselling_product_cache'
```
