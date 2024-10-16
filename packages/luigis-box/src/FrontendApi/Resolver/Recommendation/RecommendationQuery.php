<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\FrontendApi\Resolver\Recommendation;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\CategoryFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxCategoryFeedItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient;
use Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory;
use Shopsys\LuigisBoxBundle\Model\Type\RecommendationTypeEnum;
use Shopsys\LuigisBoxBundle\Model\Type\TypeInLuigisBoxEnum;
use Shopsys\ProductFeed\LuigisBoxBundle\Model\FeedItem\LuigisBoxProductFeedItem;

class RecommendationQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\LuigisBoxBundle\Component\LuigisBox\LuigisBoxClient $luigisBoxClient
     * @param \Shopsys\LuigisBoxBundle\Model\Batch\LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFrontendLimitProvider $productFrontendLimitProvider
     */
    public function __construct(
        protected readonly ProductElasticsearchProvider $productElasticsearchProvider,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly LuigisBoxClient $luigisBoxClient,
        protected readonly LuigisBoxBatchLoadDataFactory $luigisBoxBatchLoadDataFactory,
        protected readonly Domain $domain,
        protected readonly ProductFrontendLimitProvider $productFrontendLimitProvider,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return array
     */
    public function recommendationQuery(Argument $argument): array
    {
        $type = $argument['recommendationType'];
        $limit = min($argument['limit'], $this->productFrontendLimitProvider->getProductsFrontendLimit());
        $userIdentifier = $argument['userIdentifier'];
        $recommenderClientIdentifier = $argument['recommenderClientIdentifier'];
        $itemIds = [];

        if (isset($argument['itemUuids']) && count($argument['itemUuids']) > 0) {
            if ($type === RecommendationTypeEnum::CATEGORY) {
                $categoryUuid = $argument['itemUuids'][0];

                $category = $this->categoryFacade->getVisibleOnDomainByUuid($this->domain->getId(), $categoryUuid);
                $itemIds[] = LuigisBoxCategoryFeedItem::UNIQUE_IDENTIFIER_PREFIX . '-' . $category->getId();
            } else {
                $productIds = $this->productElasticsearchProvider->getSellableProductIdsByUuids($argument['itemUuids']);

                foreach ($productIds as $productId) {
                    $itemIds[] = LuigisBoxProductFeedItem::UNIQUE_IDENTIFIER_PREFIX . '-' . $productId;
                }
            }
        }

        $luigisBoxRecommendationBatchLoadData = $this->luigisBoxBatchLoadDataFactory->createForRecommendation($type, $limit, $itemIds, $userIdentifier, $recommenderClientIdentifier);

        $luigisBoxResult = $this->luigisBoxClient->getData(
            $luigisBoxRecommendationBatchLoadData,
            [TypeInLuigisBoxEnum::PRODUCT => $limit],
        )[TypeInLuigisBoxEnum::PRODUCT];

        return $this->productElasticsearchProvider->getSellableProductArrayByIds($luigisBoxResult->getIds(), $limit);
    }
}
