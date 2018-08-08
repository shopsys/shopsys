<?php

namespace Shopsys\FrameworkBundle\Model\LegalConditions;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

class LegalConditionsFacade
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleFacade
     */
    protected $articleFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    public function __construct(
        ArticleFacade $articleFacade,
        Setting $setting,
        Domain $domain
    ) {
        $this->articleFacade = $articleFacade;
        $this->setting = $setting;
        $this->domain = $domain;
    }

    public function findTermsAndConditions(int $domainId): ?\Shopsys\FrameworkBundle\Model\Article\Article
    {
        return $this->findArticle(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $domainId);
    }

    public function setTermsAndConditions(Article $termsAndConditions = null, int $domainId): void
    {
        $this->setArticle(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $termsAndConditions, $domainId);
    }

    public function getTermsAndConditionsDownloadFilename(): string
    {
        return t('Terms-and-conditions.html');
    }

    public function findPrivacyPolicy(int $domainId): ?\Shopsys\FrameworkBundle\Model\Article\Article
    {
        return $this->findArticle(Setting::PRIVACY_POLICY_ARTICLE_ID, $domainId);
    }

    public function setPrivacyPolicy(Article $privacyPolicy = null, int $domainId): void
    {
        $this->setArticle(Setting::PRIVACY_POLICY_ARTICLE_ID, $privacyPolicy, $domainId);
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

    protected function findArticle(string $settingKey, int $domainId): ?\Shopsys\FrameworkBundle\Model\Article\Article
    {
        $articleId = $this->setting->getForDomain($settingKey, $domainId);

        if ($articleId !== null) {
            return $this->articleFacade->getById($articleId);
        }

        return null;
    }

    protected function setArticle(string $settingKey, Article $privacyPolicy = null, int $domainId): void
    {
        $articleId = null;
        if ($privacyPolicy !== null) {
            $articleId = $privacyPolicy->getId();
        }

        $this->setting->setForDomain($settingKey, $articleId, $domainId);
    }
}
