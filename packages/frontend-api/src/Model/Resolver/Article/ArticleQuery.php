<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Article\Exception\ArticleNotFoundUserError;

class ArticleQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Article\Elasticsearch\ArticleElasticsearchFacade $articleElasticsearchFacade
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ArticleElasticsearchFacade $articleElasticsearchFacade,
        protected readonly Setting $setting,
    ) {
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return array
     */
    public function articleByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): array
    {
        try {
            if ($uuid !== null) {
                $articleData = $this->articleElasticsearchFacade->getByUuid($uuid);
            } elseif ($urlSlug !== null) {
                $articleData = $this->articleElasticsearchFacade->getBySlug($urlSlug);
            } else {
                throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
            }
        } catch (ArticleNotFoundException $articleNotFoundException) {
            throw new ArticleNotFoundUserError($articleNotFoundException->getMessage());
        }

        return $articleData;
    }

    /**
     * @return string
     */
    public function termsAndConditionsArticleUrlQuery(): string
    {
        return '/' . $this->getSpecialArticle(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, 'terms-and-conditions')['mainSlug'];
    }

    /**
     * @return string
     */
    public function privacyPolicyArticleUrlQuery(): string
    {
        return '/' . $this->getSpecialArticle(Setting::PRIVACY_POLICY_ARTICLE_ID, 'privacy-policy')['mainSlug'];
    }

    /**
     * @return string
     */
    public function userConsentPolicyArticleUrlQuery(): string
    {
        return '/' . $this->getSpecialArticle(Setting::USER_CONSENT_POLICY_ARTICLE_ID, 'user-consent-policy')['mainSlug'];
    }

    /**
     * @param string $settingName
     * @param string $articleIdentifier
     * @return array
     */
    protected function getSpecialArticle(string $settingName, string $articleIdentifier): array
    {
        try {
            $specialArticleId = $this->setting->getForDomain($settingName, $this->domain->getId());

            if ($specialArticleId === null) {
                throw new ArticleNotFoundUserError(sprintf('Special article setting "%s" is not set', $settingName), $articleIdentifier);
            }

            return $this->articleElasticsearchFacade->getById($specialArticleId);
        } catch (ArticleNotFoundException | SettingValueNotFoundException $exception) {
            throw new ArticleNotFoundUserError($exception->getMessage(), $articleIdentifier);
        }
    }
}
