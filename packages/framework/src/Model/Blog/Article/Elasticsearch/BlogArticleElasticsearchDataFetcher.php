<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch;

use Shopsys\FrameworkBundle\Component\Elasticsearch\AbstractElasticsearchDataFetcher;

class BlogArticleElasticsearchDataFetcher extends AbstractElasticsearchDataFetcher
{
    /**
     * @param array $data
     * @return array
     */
    protected function fillEmptyFields(array $data): array
    {
        $result = $data;

        $result['name'] = $data['name'] ?? '';
        $result['text'] = $data['text'] ?? null;
        $result['url'] = $data['url'] ?? '';
        $result['uuid'] = $data['uuid'] ?? '';
        $result['createdAt'] = $data['createdAt'] ?? '1970-01-01 00:00:00';
        $result['visibleOnHomepage'] = $data['visibleOnHomepage'] ?? false;
        $result['publishedAt'] = $data['publishedAt'] ?? '1970-01-01';
        $result['perex'] = $data['perex'] ?? null;
        $result['seoTitle'] = $data['seoTitle'] ?? null;
        $result['seoMetaDescription'] = $data['seoMetaDescription'] ?? null;
        $result['seoH1'] = $data['seoH1'] ?? null;
        $result['categories'] = $data['categories'] ?? [];
        $result['mainSlug'] = $data['mainSlug'] ?? '';
        $result['products'] = $data['products'] ?? [];
        $result['imageUrl'] = $data['imageUrl'] ?? null;

        return $result;
    }
}
