<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;

class ArticleResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Article\ArticleFacade
     */
    protected $articleFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Setting\Setting
     */
    protected $setting;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     */
    public function __construct(
        ArticleFacade $articleFacade,
        Domain $domain,
        Setting $setting
    ) {
        $this->articleFacade = $articleFacade;
        $this->domain = $domain;
        $this->setting = $setting;
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function resolver(string $uuid): Article
    {
        if (Uuid::isValid($uuid) === false) {
            throw new UserError('Provided argument is not valid UUID.');
        }

        try {
            return $this->articleFacade->getVisibleByDomainIdAndUuid($this->domain->getId(), $uuid);
        } catch (ArticleNotFoundException $articleNotFoundException) {
            throw new UserError($articleNotFoundException->getMessage());
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function termsAndConditionsArticle(): Article
    {
        try {
            $articleId = $this->setting->getForDomain(
                Setting::TERMS_AND_CONDITIONS_ARTICLE_ID,
                $this->domain->getId()
            );
            return $this->articleFacade->getById($articleId);
        } catch (ArticleNotFoundException $articleNotFoundException) {
            throw new UserError($articleNotFoundException->getMessage());
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function privacyPolicyArticle(): Article
    {
        try {
            $articleId = $this->setting->getForDomain(
                Setting::PRIVACY_POLICY_ARTICLE_ID,
                $this->domain->getId()
            );
            return $this->articleFacade->getById($articleId);
        } catch (ArticleNotFoundException $articleNotFoundException) {
            throw new UserError($articleNotFoundException->getMessage());
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function cookiesArticle(): Article
    {
        try {
            $articleId = $this->setting->getForDomain(
                Setting::COOKIES_ARTICLE_ID,
                $this->domain->getId()
            );
            return $this->articleFacade->getById($articleId);
        } catch (ArticleNotFoundException $articleNotFoundException) {
            throw new UserError($articleNotFoundException->getMessage());
        }
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'article',
            'termsAndConditionsArticle' => 'termsAndConditionsArticle',
            'privacyPolicyArticle' => 'privacyPolicyArticle',
            'cookiesArticle' => 'cookiesArticle',
        ];
    }
}
