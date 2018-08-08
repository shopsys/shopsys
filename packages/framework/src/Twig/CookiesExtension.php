<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade;
use Twig_SimpleFunction;

class CookiesExtension extends \Twig_Extension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade
     */
    private $cookiesFacade;

    public function __construct(CookiesFacade $cookiesFacade)
    {
        $this->cookiesFacade = $cookiesFacade;
    }

    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('isCookiesConsentGiven', [$this, 'isCookiesConsentGiven']),
            new Twig_SimpleFunction('findCookiesArticleByDomainId', [$this, 'findCookiesArticleByDomainId']),
        ];
    }

    public function getName(): string
    {
        return 'cookies';
    }

    public function isCookiesConsentGiven(): bool
    {
        return $this->cookiesFacade->isCookiesConsentGiven();
    }

    public function findCookiesArticleByDomainId($domainId): ?\Shopsys\FrameworkBundle\Model\Article\Article
    {
        return $this->cookiesFacade->findCookiesArticleByDomainId($domainId);
    }
}
