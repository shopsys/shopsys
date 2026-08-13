<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\ProductReview;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

class ProductReviewPolicyFacade
{
    public function __construct(
        protected readonly ArticleFacade $articleFacade,
        protected readonly Setting $setting,
        protected readonly Domain $domain,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    public function findProductReviewPolicyArticleByDomainId(int $domainId): ?Article
    {
        $productReviewPolicyArticleId = $this->setting->getForDomain(
            Setting::PRODUCT_REVIEW_POLICY_ARTICLE_ID,
            $domainId,
        );

        if ($productReviewPolicyArticleId === null) {
            return null;
        }

        return $this->articleFacade->findById($productReviewPolicyArticleId);
    }

    public function setProductReviewPolicyArticleOnDomain(?Article $productReviewPolicyArticle, int $domainId): void
    {
        $this->setting->setForDomain(
            Setting::PRODUCT_REVIEW_POLICY_ARTICLE_ID,
            $productReviewPolicyArticle?->getId(),
            $domainId,
        );

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SETTINGS_QUERY_KEY_PART);
    }

    public function isArticleUsedAsProductReviewPolicyArticle(Article $article): bool
    {
        return array_any(
            $this->domain->getAllIds(),
            fn ($domainId) => $this->setting->getForDomain(Setting::PRODUCT_REVIEW_POLICY_ARTICLE_ID, $domainId) === $article->getId(),
        );
    }
}
