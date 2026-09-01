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
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ArticleElasticsearchFacade $articleElasticsearchFacade,
        protected readonly Setting $setting,
    ) {
    }

    public function articleByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): array
    {
        try {
            if ($uuid !== null) {
                $articleData = $this->articleElasticsearchFacade->getByUuid($uuid);
            } elseif ($urlSlug !== null) {
                $articleData = $this->articleElasticsearchFacade->getSiteArticleBySlug(ltrim($urlSlug, '/'));
            } else {
                throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
            }
        } catch (ArticleNotFoundException $articleNotFoundException) {
            throw new ArticleNotFoundUserError($articleNotFoundException->getMessage());
        }

        return $this->sanitizeDraggableAttribute($articleData);
    }

    public function termsAndConditionsArticleUrlQuery(): string
    {
        return '/' . $this->getSpecialArticle(Setting::TERMS_AND_CONDITIONS_ARTICLE_ID, 'terms-and-conditions')['mainSlug'];
    }

    public function privacyPolicyArticleUrlQuery(): string
    {
        return '/' . $this->getSpecialArticle(Setting::PRIVACY_POLICY_ARTICLE_ID, 'privacy-policy')['mainSlug'];
    }

    public function userConsentPolicyArticleUrlQuery(): string
    {
        return '/' . $this->getSpecialArticle(Setting::USER_CONSENT_POLICY_ARTICLE_ID, 'user-consent-policy')['mainSlug'];
    }

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
