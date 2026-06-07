<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Article;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\CombinedArticle\CombinedArticleElasticsearchFacade;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxResult;

class LuigisBoxArticleSearchResultsMapper
{
    public function __construct(
        protected readonly CombinedArticleElasticsearchFacade $combinedArticleElasticsearchFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mapArticleData(LuigisBoxResult $luigisBoxResult): array
    {
        if (count($luigisBoxResult->getIdsWithPrefix()) === 0) {
            return [];
        }

        /** @var array<string, array<int, string>> $idsByType */
        $idsByType = [];

        foreach ($luigisBoxResult->getIdsWithPrefix() as $idWithPrefix) {
            [$type, $id] = explode('-', $idWithPrefix);
            $idsByType[$type][] = $id;
        }

        return $this->combinedArticleElasticsearchFacade->getArticlesByIds(
            $idsByType,
            $this->domain->getId(),
            count($luigisBoxResult->getIds()),
        );
    }
}
