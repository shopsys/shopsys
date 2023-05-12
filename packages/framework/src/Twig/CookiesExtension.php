<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CookiesExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade $cookiesFacade
     */
    public function __construct(protected readonly CookiesFacade $cookiesFacade)
    {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('isCookiesConsentGiven', [$this, 'isCookiesConsentGiven']),
            new TwigFunction('findCookiesArticleByDomainId', [$this, 'findCookiesArticleByDomainId']),
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'cookies';
    }

    /**
     * @return bool
     */
    public function isCookiesConsentGiven()
    {
        return $this->cookiesFacade->isCookiesConsentGiven();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    public function findCookiesArticleByDomainId($domainId)
    {
        return $this->cookiesFacade->findCookiesArticleByDomainId($domainId);
    }
}
