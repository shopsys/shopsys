<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CookiesExtension extends AbstractExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade
     */
    protected $cookiesFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade $cookiesFacade
     */
    public function __construct(CookiesFacade $cookiesFacade)
    {
        $this->cookiesFacade = $cookiesFacade;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('isCookiesConsentGiven', [$this, 'isCookiesConsentGiven']),
            new TwigFunction('findCookiesArticleByDomainId', [$this, 'findCookiesArticleByDomainId']),
        ];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'cookies';
    }

    /**
     * @return bool
     */
    public function isCookiesConsentGiven(): bool
    {
        return $this->cookiesFacade->isCookiesConsentGiven();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Article\Article|null
     */
    public function findCookiesArticleByDomainId(int $domainId): ?Article
    {
        return $this->cookiesFacade->findCookiesArticleByDomainId($domainId);
    }
}
