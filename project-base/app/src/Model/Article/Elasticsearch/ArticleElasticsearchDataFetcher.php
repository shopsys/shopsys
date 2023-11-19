<?php

declare(strict_types=1);

namespace App\Model\Article\Elasticsearch;

use App\Component\Elasticsearch\AbstractElasticsearchDataFetcher;

class ArticleElasticsearchDataFetcher extends AbstractElasticsearchDataFetcher
{
    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    protected function fillEmptyFields(array $data): array
    {
        $result = $data;

        $result['uuid'] = $data['uuid'] ?? '';
        $result['placement'] = $data['placement'] ?? '';
        $result['name'] = $data['name'] ?? '';
        $result['text'] = $data['text'] ?? null;
        $result['seoH1'] = $data['seoH1'] ?? null;
        $result['seoTitle'] = $data['seoTitle'] ?? null;
        $result['seoMetaDescription'] = $data['seoMetaDescription'] ?? null;
        $result['mainSlug'] = $data['mainSlug'] ?? '';
        $result['position'] = $data['position'] ?? '';

        return $result;
    }
}
