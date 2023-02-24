<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Article;

use App\Component\Setting\Setting;
use App\Model\Article\Elasticsearch\ArticleElasticsearchFacade;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\QueryInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Exception\SettingValueNotFoundException;
use Shopsys\FrameworkBundle\Component\Setting\Setting as BaseSetting;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;

//use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
//use Shopsys\FrontendApiBundle\Model\Resolver\Article\Exception\ArticleNotFoundUserError;

class ArticleResolver implements QueryInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Component\Setting\Setting
     */
    private Setting $setting;

    /**
     * @var \App\Model\Article\Elasticsearch\ArticleElasticsearchFacade
     */
    private ArticleElasticsearchFacade $articleElasticsearchFacade;

    /**
     * @param \App\Model\Article\Elasticsearch\ArticleElasticsearchFacade $articleElasticsearchFacade
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ArticleElasticsearchFacade $articleElasticsearchFacade,
        Setting $setting,
        Domain $domain
    ) {
        $this->articleElasticsearchFacade = $articleElasticsearchFacade;
        $this->setting = $setting;
        $this->domain = $domain;
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return array
     */
    public function resolveArticle(?string $uuid = null, ?string $urlSlug = null): array
    {
        try {
            if ($uuid !== null) {
                $articleData = $this->articleElasticsearchFacade->getByUuid($uuid);
            } elseif ($urlSlug !== null) {
                $articleData = $this->articleElasticsearchFacade->getBySlug($urlSlug);
            } else {
                // TODO-RK                throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
                throw new UserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
            }
        } catch (ArticleNotFoundException $articleNotFoundException) {
            //  TODO-RK           throw new ArticleNotFoundUserError($articleNotFoundException->getMessage());
            throw new UserError($articleNotFoundException->getMessage());
        }

        return $articleData;
    }

    /**
     * @return array
     */
    public function resolveTermsAndConditionsArticle(): array
    {
        return $this->getSpecialArticle(BaseSetting::TERMS_AND_CONDITIONS_ARTICLE_ID);
    }

    /**
     * @return array
     */
    public function resolvePrivacyPolicyArticle(): array
    {
        return $this->getSpecialArticle(BaseSetting::PRIVACY_POLICY_ARTICLE_ID);
    }

    /**
     * @return array
     */
    public function resolveCookiesArticle(): array
    {
        return $this->getSpecialArticle(BaseSetting::COOKIES_ARTICLE_ID);
    }

    /**
     * @param string $settingName
     * @return array
     */
    private function getSpecialArticle(string $settingName): array
    {
        try {
            $specialArticleId = $this->setting->getForDomain($settingName, $this->domain->getId());
            if ($specialArticleId === null) {
                //TODO-RK                throw new ArticleNotFoundUserError(sprintf('Special article setting "%s" is not set', $settingName));
                throw new UserError(sprintf('Special article setting "%s" is not set', $settingName));
            }
            return $this->articleElasticsearchFacade->getById($specialArticleId);
        } catch (ArticleNotFoundException|SettingValueNotFoundException $exception) {
            //TODO-RK            throw new ArticleNotFoundUserError($exception->getMessage());
            throw new UserError($exception->getMessage());
        }
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolveArticle' => 'resolveArticle',
            'resolveTermsAndConditionsArticle' => 'resolveTermsAndConditionsArticle',
            'resolvePrivacyPolicyArticle' => 'resolvePrivacyPolicyArticle',
            'resolveCookiesArticle' => 'resolveCookiesArticle',
        ];
    }
}
