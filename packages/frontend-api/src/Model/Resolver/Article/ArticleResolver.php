<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrontendApiBundle\Model\Article\ArticleFacade;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Article\Exception\ArticleNotFoundUserError;

class ArticleResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrontendApiBundle\Model\Article\ArticleFacade
     */
    protected $articleFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade
     */
    protected $legalConditionsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade
     */
    protected $cookiesFacade;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade
     */
    protected FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Article\ArticleFacade $articleFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade $cookiesFacade
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        ArticleFacade $articleFacade,
        Domain $domain,
        LegalConditionsFacade $legalConditionsFacade,
        CookiesFacade $cookiesFacade,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        $this->articleFacade = $articleFacade;
        $this->domain = $domain;
        $this->legalConditionsFacade = $legalConditionsFacade;
        $this->cookiesFacade = $cookiesFacade;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function resolver(?string $uuid = null, ?string $urlSlug = null): Article
    {
        if ($uuid !== null) {
            return $this->getVisibleByDomainIdAndUuid($uuid);
        }

        if ($urlSlug !== null) {
            return $this->getVisibleByDomainIdAndSlug($urlSlug);
        }

        throw new UserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function termsAndConditionsArticle(): Article
    {
        $article = $this->legalConditionsFacade->findTermsAndConditions($this->domain->getId());

        if ($article === null) {
            throw new ArticleNotFoundUserError('Terms and condition article was not found', 'terms-and-conditions');
        }

        return $article;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function privacyPolicyArticle(): Article
    {
        $article = $this->legalConditionsFacade->findPrivacyPolicy($this->domain->getId());

        if ($article === null) {
            throw new ArticleNotFoundUserError('Privacy policy article was not found', 'privacy-policy');
        }

        return $article;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function cookiesArticle(): Article
    {
        $article = $this->cookiesFacade->findCookiesArticleByDomainId($this->domain->getId());

        if ($article === null) {
            throw new ArticleNotFoundUserError('Information about cookies article was not found', 'cookies');
        }

        return $article;
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

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    protected function getVisibleByDomainIdAndUuid(string $uuid): Article
    {
        try {
            return $this->articleFacade->getVisibleByDomainIdAndUuid($this->domain->getId(), $uuid);
        } catch (ArticleNotFoundException $articleNotFoundException) {
            throw new ArticleNotFoundUserError($articleNotFoundException->getMessage());
        }
    }

    /**
     * @param string $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    protected function getVisibleByDomainIdAndSlug(string $urlSlug): Article
    {
        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_article_detail',
                $urlSlug
            );

            return $this->articleFacade->getVisibleByDomainIdAndId(
                $this->domain->getId(),
                $friendlyUrl->getEntityId()
            );
        } catch (FriendlyUrlNotFoundException | ArticleNotFoundException $articleNotFoundException) {
            throw new ArticleNotFoundUserError('Article with URL slug `' . $urlSlug . '` does not exist.');
        }
    }
}
