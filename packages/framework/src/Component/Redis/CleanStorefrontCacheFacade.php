<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Redis;

use Redis;

class CleanStorefrontCacheFacade
{
    public const string TRANSLATION_VERSION_KEY = 'translates:version';

    public const string NAVIGATION_QUERY_KEY_PART = 'NavigationQuery';
    public const string BLOG_ARTICLES_QUERY_KEY_PART = 'BlogArticlesQuery';
    public const string BLOG_CATEGORIES_QUERY_KEY_PART = 'BlogCategories';
    public const string ARTICLES_QUERY_KEY_PART = 'ArticlesQuery';
    public const string SETTINGS_QUERY_KEY_PART = 'SettingsQuery';
    public const string SLIDER_ITEMS_QUERY_KEY_PART = 'SliderItemsQuery';
    public const string NOTIFICATION_BARS_QUERY_KEY_PART = 'NotificationBars';
    public const string ADVERTS_QUERY_KEY_PART = 'AdvertsQuery';
    public const string PROMOTED_PRODUCTS_QUERY_KEY_PART = 'PromotedProductsQuery';
    public const string BRANDS_QUERY_KEY_PART = 'BrandsQuery';
    public const string SEO_PAGE_QUERY_KEY_PART = 'SeoPageQuery';
    public const string STORES_QUERY_KEY_PART = 'StoresQuery';

    public function __construct(
        protected readonly Redis $storefrontGraphqlQueryClient,
    ) {
    }

    public function cleanStorefrontTranslationCache(string $locale = '', ?string $namespace = null): void
    {
        if ($namespace !== null) {
            $keyPattern = 'translates:' . $locale . ':' . $namespace . '*';
        } else {
            $keyPattern = 'translates:' . $locale . '*';
        }

        $this->cleanStorefrontCacheByKeyPattern($keyPattern);
        $this->bumpStorefrontTranslationVersion();
    }

    protected function bumpStorefrontTranslationVersion(): void
    {
        $this->storefrontGraphqlQueryClient->set(self::TRANSLATION_VERSION_KEY, (string)microtime(true));
    }

    public function cleanStorefrontGraphqlQueryCache(string $queryKey = ''): void
    {
        $keyPattern = 'queryCache:' . $queryKey . '*';
        $this->cleanStorefrontCacheByKeyPattern($keyPattern);
    }

    protected function cleanStorefrontCacheByKeyPattern(string $keyPattern): void
    {
        $prefix = (string)$this->storefrontGraphqlQueryClient->getOption(Redis::OPT_PREFIX);

        $keyPattern = $prefix . $keyPattern;
        $iterator = null;
        $toRemove = [];

        do {
            $keys = $this->storefrontGraphqlQueryClient->scan($iterator, $keyPattern, 0);

            if ($keys === false || count($keys) === 0) {
                continue;
            }

            foreach ($keys as $key) {
                $toRemove[] = str_replace($prefix, '', $key);
            }
        } while (is_numeric($iterator) && $iterator > 0);

        $this->storefrontGraphqlQueryClient->unlink($toRemove);
    }
}
