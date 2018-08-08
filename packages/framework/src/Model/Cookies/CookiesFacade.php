<?php

namespace Shopsys\FrameworkBundle\Model\Cookies;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Environment\EnvironmentType;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Symfony\Component\HttpFoundation\RequestStack;

class CookiesFacade
{
    const EU_COOKIES_COOKIE_CONSENT_NAME = 'eu-cookies';

    /**
     * @var string
     */
    protected $environment;

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
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;
    
    public function __construct(
        string $environment,
        ArticleFacade $articleFacade,
        Setting $setting,
        Domain $domain,
        RequestStack $requestStack
    ) {
        $this->environment = $environment;
        $this->articleFacade = $articleFacade;
        $this->setting = $setting;
        $this->domain = $domain;
        $this->requestStack = $requestStack;
    }

    public function findCookiesArticleByDomainId(int $domainId): ?\Shopsys\FrameworkBundle\Model\Article\Article
    {
        $cookiesArticleId = $this->setting->getForDomain(Setting::COOKIES_ARTICLE_ID, $domainId);

        if ($cookiesArticleId !== null) {
            return $this->articleFacade->findById(
                $this->setting->getForDomain(Setting::COOKIES_ARTICLE_ID, $domainId)
            );
        }

        return null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\Article|null $cookiesArticle
     */
    public function setCookiesArticleOnDomain(?Article $cookiesArticle, int $domainId): void
    {
        $cookiesArticleId = null;
        if ($cookiesArticle !== null) {
            $cookiesArticleId = $cookiesArticle->getId();
        }
        $this->setting->setForDomain(
            Setting::COOKIES_ARTICLE_ID,
            $cookiesArticleId,
            $domainId
        );
    }

    public function isArticleUsedAsCookiesInfo(Article $article): bool
    {
        foreach ($this->domain->getAll() as $domainConfig) {
            if ($this->findCookiesArticleByDomainId($domainConfig->getId()) === $article) {
                return true;
            }
        }

        return false;
    }

    public function isCookiesConsentGiven(): bool
    {
        // Cookie fixed bar overlays some elements in viewport and mouseover fails on these elements in acceptance tests.
        if ($this->environment === EnvironmentType::TEST) {
            return true;
        }
        $masterRequest = $this->requestStack->getMasterRequest();

        return $masterRequest->cookies->has(self::EU_COOKIES_COOKIE_CONSENT_NAME);
    }
}
