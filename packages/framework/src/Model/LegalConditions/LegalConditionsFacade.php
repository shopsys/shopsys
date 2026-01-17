<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\LegalConditions;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

abstract class LegalConditionsFacade
{
    public function __construct(
        protected readonly ArticleFacade $articleFacade,
        protected readonly Setting $setting,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    public function findTermsAndConditions($domainId)
    {
        return $this->findArticle(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $domainId);
    }

    public function setTermsAndConditions(int $domainId, ?Article $termsAndConditions = null): void
    {
        $this->setArticle(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $domainId, $termsAndConditions);
    }

    /**
     * @return string
     */
    abstract public function getTermsAndConditionsDownloadFilename();

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    public function findPrivacyPolicy($domainId)
    {
        return $this->findArticle(Setting::PRIVACY_POLICY_ARTICLE_ID, $domainId);
    }

    public function setPrivacyPolicy(int $domainId, ?Article $privacyPolicy = null): void
    {
        $this->setArticle(Setting::PRIVACY_POLICY_ARTICLE_ID, $domainId, $privacyPolicy);
    }

    /**
     * @return bool
     */
    public function isArticleUsedAsLegalConditions(Article $article)
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

    /**
     * @param string $settingKey
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    protected function findArticle($settingKey, $domainId)
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
    }
}
