<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;

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
     * @var \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade
     */
    protected $legalConditionsFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade
     */
    protected $cookiesFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade $cookiesFacade
     */
    public function __construct(
        ArticleFacade $articleFacade,
        Domain $domain,
        LegalConditionsFacade $legalConditionsFacade,
        CookiesFacade $cookiesFacade
    ) {
        $this->articleFacade = $articleFacade;
        $this->domain = $domain;
        $this->legalConditionsFacade = $legalConditionsFacade;
        $this->cookiesFacade = $cookiesFacade;
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
        $article = $this->legalConditionsFacade->findTermsAndConditions($this->domain->getId());

        if ($article === null) {
            throw new UserError('Terms and condition article was not found');
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
            throw new UserError('Privacy policy article was not found');
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
            throw new UserError('Information about cookies article was not found');
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
}
