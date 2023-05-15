<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrontendApiBundle\Model\Article\ArticleFacade;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Article\Exception\ArticleNotFoundUserError;

class ArticleQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Article\ArticleFacade $articleFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade $cookiesFacade
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        protected readonly ArticleFacade $articleFacade,
        protected readonly Domain $domain,
        protected readonly LegalConditionsFacade $legalConditionsFacade,
        protected readonly CookiesFacade $cookiesFacade,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function articleByUuidOrUrlSlugQuery(?string $uuid = null, ?string $urlSlug = null): Article
    {
        if ($uuid !== null) {
            return $this->getVisibleByDomainIdAndUuid($uuid);
        }

        if ($urlSlug !== null) {
            return $this->getVisibleByDomainIdAndSlug($urlSlug);
        }

        throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article
     */
    public function termsAndConditionsArticleQuery(): Article
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
    public function privacyPolicyArticleQuery(): Article
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
    public function cookiesArticleQuery(): Article
    {
        $article = $this->cookiesFacade->findCookiesArticleByDomainId($this->domain->getId());

        if ($article === null) {
            throw new ArticleNotFoundUserError('Information about cookies article was not found', 'cookies');
        }

        return $article;
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
                $urlSlug,
            );

            return $this->articleFacade->getVisibleByDomainIdAndId(
                $this->domain->getId(),
                $friendlyUrl->getEntityId(),
            );
        } catch (FriendlyUrlNotFoundException | ArticleNotFoundException $articleNotFoundException) {
            throw new ArticleNotFoundUserError('Article with URL slug `' . $urlSlug . '` does not exist.');
        }
    }
}
