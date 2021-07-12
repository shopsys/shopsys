<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Elasticsearch;

/**
 * Heavily inspired by @see \App\Model\Product\Search\ProductElasticsearchConverter
 */
class BlogArticleElasticsearchConverter
{
    /**
     * @param array $blogArticle
     * @return array
     */
    public function fillEmptyFields(array $blogArticle): array
    {
        $result = $blogArticle;

        $result['name'] = $blogArticle['name'] ?? null;
        $result['text'] = $blogArticle['text'] ?? null;
        $result['url'] = $blogArticle['url'] ?? '';
        $result['uuid'] = $blogArticle['uuid'] ?? '';
        $result['createdAt'] = $blogArticle['createdAt'] ?? '1970-01-01 00:00:00';
        $result['visibleOnHomepage'] = $blogArticle['visibleOnHomepage'] ?? false;
        $result['publishDate'] = $blogArticle['publishDate'] ?? '1970-01-01';
        $result['perex'] = $blogArticle['perex'] ?? null;
        $result['seoTitle'] = $blogArticle['seoTitle'] ?? null;
        $result['seoMetaDescription'] = $blogArticle['seoMetaDescription'] ?? null;
        $result['seoH1'] = $blogArticle['seoH1'] ?? null;
        $result['categories'] = $blogArticle['categories'] ?? [];

        return $result;
    }
}
