<?php

namespace Shopsys\FrameworkBundle\Model\LegalConditions;

use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;

/**
 * @deprecated Class will be changed to abstract class in next major version. Extend this class to your project and implement corresponding methods instead.
 */
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
        if (static::class === self::class) {
            DeprecationHelper::triggerAbstractClass(self::class);
        }

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
     * @param \Shopsys\FrameworkBundle\Model\Article\Article|null $termsAndConditions
     * @param int $domainId
     */
    public function setTermsAndConditions(?Article $termsAndConditions = null, $domainId)
    {
        $this->setArticle(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, $termsAndConditions, $domainId);
    }

    /**
     * @deprecated Method will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.
     * @return string
     */
    public function getTermsAndConditionsDownloadFilename()
    {
        DeprecationHelper::triggerAbstractMethod(__METHOD__);

        return t('Terms-and-conditions.html');
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    public function findPrivacyPolicy($domainId)
    {
        return $this->findArticle(Setting::PRIVACY_POLICY_ARTICLE_ID, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Article|null $privacyPolicy
     * @param int $domainId
     */
    public function setPrivacyPolicy(?Article $privacyPolicy = null, $domainId)
    {
        $this->setArticle(Setting::PRIVACY_POLICY_ARTICLE_ID, $privacyPolicy, $domainId);
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
     * @param \Shopsys\FrameworkBundle\Model\Article\Article|null $privacyPolicy
     * @param int $domainId
     */
    protected function setArticle($settingKey, ?Article $privacyPolicy = null, $domainId)
    {
        $articleId = null;
        if ($privacyPolicy !== null) {
            $articleId = $privacyPolicy->getId();
        }

        $this->setting->setForDomain($settingKey, $articleId, $domainId);
    }
}
