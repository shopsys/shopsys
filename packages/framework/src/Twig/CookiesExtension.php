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

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('isCookiesConsentGiven', [$this, 'isCookiesConsentGiven']),
            new Twig_SimpleFunction('findCookiesArticleByDomainId', [$this, 'findCookiesArticleByDomainId']),
        ];
    }

    public function getName()
    {
        return 'cookies';
    }

    public function isCookiesConsentGiven()
    {
        return $this->cookiesFacade->isCookiesConsentGiven();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    public function findCookiesArticleByDomainId($domainId)
    {
        return $this->cookiesFacade->findCookiesArticleByDomainId($domainId);
    }
}
