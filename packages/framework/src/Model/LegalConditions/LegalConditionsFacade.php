<?php

namespace Shopsys\FrameworkBundle\Model\LegalConditions;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

abstract class LegalConditionsFacade
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ArticleFacade $articleFacade,
        Setting $setting,
        Domain $domain
    ) {
        $this->articleFacade = $articleFacade;
        $this->setting = $setting;
        $this->domain = $domain;
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    public function findTermsAndConditions($domainId)
    {
        return $this->findArticle(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $domainId);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Article\Article|null $termsAndConditions
     */
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

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Article\Article|null $privacyPolicy
     */
    public function setPrivacyPolicy(int $domainId, ?Article $privacyPolicy = null): void
    {
        $this->setArticle(Setting::PRIVACY_POLICY_ARTICLE_ID, $domainId, $privacyPolicy);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Article $article
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

    /**
     * @param string $settingKey
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Article\Article|null $article
     */
    protected function setArticle(string $settingKey, int $domainId, ?Article $article = null): void
    {
        $articleId = null;
        if ($article !== null) {
            $articleId = $article->getId();
        }

        $this->setting->setForDomain($settingKey, $articleId, $domainId);
    }
}
