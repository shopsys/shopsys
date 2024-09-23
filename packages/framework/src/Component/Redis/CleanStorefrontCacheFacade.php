<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Redis;

use Redis;

class CleanStorefrontCacheFacade
{
    public const string NAVIGATION_QUERY_KEY_PART = 'NavigationQuery';
    public const string BLOG_ARTICLES_QUERY_KEY_PART = 'BlogArticlesQuery';
    public const string BLOG_CATEGORIES_QUERY_KEY_PART = 'BlogCategories';
    public const string ARTICLES_QUERY_KEY_PART = 'ArticlesQuery';
    public const string SETTINGS_QUERY_KEY_PART = 'SettingsQuery';
    public const string SLIDER_ITEMS_QUERY_KEY_PART = 'SliderItemsQuery';

    /**
     * @param \Redis $storefrontGraphqlQueryClient
     */
    public function __construct(
        protected readonly Redis $storefrontGraphqlQueryClient,
    ) {
    }

    /**
     * @param string $locale
     */
    public function cleanStorefrontTranslationCache(string $locale = ''): void
    {
        $keyPattern = 'translates:' . $locale . '*';
        $this->cleanStorefrontCacheByKeyPattern($keyPattern);
    }

    /**
     * @param string $queryKey
     */
    public function cleanStorefrontGraphqlQueryCache(string $queryKey = ''): void
    {
        $keyPattern = 'queryCache:' . $queryKey . '*';
        $this->cleanStorefrontCacheByKeyPattern($keyPattern);
    }

    /**
     * @param string $keyPattern
     */
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
