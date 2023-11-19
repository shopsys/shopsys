<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Article;

use App\Component\Setting\Setting;
use App\Model\Article\Elasticsearch\ArticleElasticsearchFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting as BaseSetting;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Article\Exception\ArticleNotFoundUserError;

/**
 * @method \App\Model\Article\Article articleByUuidOrUrlSlugQuery(string|null $uuid = null, string|null $urlSlug = null)
 * @method \App\Model\Article\Article getVisibleByDomainIdAndUuid(string $uuid)
 * @method \App\Model\Article\Article getVisibleByDomainIdAndSlug(string $urlSlug)
 */
class ArticleQuery extends AbstractQuery
{
    /**
     * @param \App\Model\Article\Elasticsearch\ArticleElasticsearchFacade $articleElasticsearchFacade
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private readonly ArticleElasticsearchFacade $articleElasticsearchFacade,
        private readonly Setting $setting,
        private readonly Domain $domain,
    ) {
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return mixed[]
     */
    public function articleQuery(?string $uuid = null, ?string $urlSlug = null): array
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
     * @return mixed[]
     */
    public function termsAndConditionsArticleQuery(): array
    {
        return $this->getSpecialArticle(BaseSetting::TERMS_AND_CONDITIONS_ARTICLE_ID);
    }

    /**
     * @return mixed[]
     */
    public function privacyPolicyArticleQuery(): array
    {
        return $this->getSpecialArticle(BaseSetting::PRIVACY_POLICY_ARTICLE_ID);
    }

    /**
     * @return mixed[]
     */
    public function cookiesArticleQuery(): array
    {
        return $this->getSpecialArticle(BaseSetting::COOKIES_ARTICLE_ID);
    }

    /**
     * @param string $settingName
     * @return mixed[]
     */
    private function getSpecialArticle(string $settingName): array
    {
        try {
            $specialArticleId = $this->setting->getForDomain($settingName, $this->domain->getId());

            if ($specialArticleId === null) {
                throw new ArticleNotFoundUserError(sprintf('Special article setting "%s" is not set', $settingName));
            }

            return $this->articleElasticsearchFacade->getById($specialArticleId);
        } catch (ArticleNotFoundException|SettingValueNotFoundException $exception) {
            throw new ArticleNotFoundUserError($exception->getMessage());
        }
    }
}
