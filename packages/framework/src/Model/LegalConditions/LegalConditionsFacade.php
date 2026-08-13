<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LegalConditions;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

abstract class LegalConditionsFacade
{
    public function __construct(
        protected readonly ArticleFacade $articleFacade,
        protected readonly Setting $setting,
        protected readonly Domain $domain,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    public function findTermsAndConditions(int $domainId): ?Article
    {
        return $this->findArticle(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $domainId);
    }

    public function setTermsAndConditions(int $domainId, ?Article $termsAndConditions = null): void
    {
        $this->setArticle(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $domainId, $termsAndConditions);
    }

    abstract public function getTermsAndConditionsDownloadFilename(): string;

    public function findPrivacyPolicy(int $domainId): ?Article
    {
        return $this->findArticle(Setting::PRIVACY_POLICY_ARTICLE_ID, $domainId);
    }

    public function setPrivacyPolicy(int $domainId, ?Article $privacyPolicy = null): void
    {
        $this->setArticle(Setting::PRIVACY_POLICY_ARTICLE_ID, $domainId, $privacyPolicy);
    }

    public function isArticleUsedAsLegalConditions(Article $article): bool
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $legalConditionsArticles = [
                $this->findTermsAndConditions($domainId),
                $this->findPrivacyPolicy($domainId),
            ];

            if (in_array($article, $legalConditionsArticles, true)) {
                return true;
            }
        }

        return false;
    }

    protected function findArticle(string $settingKey, int $domainId): ?Article
    {
        $articleId = $this->setting->getForDomain($settingKey, $domainId);

        if ($articleId !== null) {
            return $this->articleFacade->findById($articleId);
        }

        return null;
    }

    protected function setArticle(string $settingKey, int $domainId, ?Article $article = null): void
    {
        $articleId = null;

        if ($article !== null) {
            $articleId = $article->getId();
        }

        $this->setting->setForDomain($settingKey, $articleId, $domainId);

        $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::SETTINGS_QUERY_KEY_PART);
    }
}
