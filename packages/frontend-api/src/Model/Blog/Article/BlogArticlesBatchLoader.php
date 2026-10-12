<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Blog\Article;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Model\Blog\Article\Elasticsearch\FilterQueryFactory;

class BlogArticlesBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly BlogArticleElasticsearchBatchRepository $blogArticleElasticsearchBatchRepository,
        protected readonly BlogArticlesByBlogCategoryBatchLoadResultFactory $blogArticlesByBlogCategoryBatchLoadResultFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Blog\Article\BlogArticlesByBlogCategoryBatchLoadData[] $batchLoadDataList
     */
    public function loadByBlogCategoriesBatchLoadData(array $batchLoadDataList): Promise
    {
        $filterQueries = [];

        foreach ($batchLoadDataList as $batchLoadData) {
            $filterQueries[] = $this->filterQueryFactory->createPublishedByBlogCategory(
                $batchLoadData->blogCategory,
                $batchLoadData->offset,
                $batchLoadData->limit,
                $batchLoadData->onlyVisibleOnHomepage,
            );
        }

        $batchedResult = $this->blogArticleElasticsearchBatchRepository->getBatchedHitsAndTotalsByFilterQueries($filterQueries);
        $results = [];

        foreach ($batchedResult[BlogArticleElasticsearchBatchRepository::HITS_KEY] as $key => $blogArticles) {
            $results[] = $this->blogArticlesByBlogCategoryBatchLoadResultFactory->create(
                $blogArticles,
                $batchedResult[BlogArticleElasticsearchBatchRepository::TOTALS_KEY][$key],
            );
        }

        return $this->promiseAdapter->all($results);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategory[] $blogCategories
     */
    public function loadTotalCountByBlogCategories(array $blogCategories): Promise
    {
        $filterQueries = [];

        foreach ($blogCategories as $blogCategory) {
            $filterQueries[] = $this->filterQueryFactory->createPublishedByBlogCategory($blogCategory, 0, 0);
        }

        $batchedResult = $this->blogArticleElasticsearchBatchRepository->getBatchedHitsAndTotalsByFilterQueries($filterQueries);

        return $this->promiseAdapter->all(array_values($batchedResult[BlogArticleElasticsearchBatchRepository::TOTALS_KEY]));
    }
}
