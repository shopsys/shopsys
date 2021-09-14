<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\PersonalData;

use App\Component\Setting\Setting;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Component\Setting\Setting as BaseSetting;

class PersonalDataPageResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \App\Component\Setting\Setting
     */
    private Setting $setting;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\DomainRouter
     */
    private DomainRouter $router;

    /**
     * @param \App\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     */
    public function __construct(Setting $setting, Domain $domain, DomainRouterFactory $domainRouterFactory)
    {
        $this->setting = $setting;
        $this->domain = $domain;
        $this->router = $domainRouterFactory->getRouter($this->domain->getId());
    }

    /**
     * @return array<string, string>
     */
    public function resolve(): array
    {
        return [
            'displaySiteContent' => $this->setting->getForDomain(BaseSetting::PERSONAL_DATA_DISPLAY_SITE_CONTENT, $this->domain->getId()),
            'displaySiteSlug' => $this->router->generate('front_personal_data', []),
            'exportSiteContent' => $this->setting->getForDomain(BaseSetting::PERSONAL_DATA_EXPORT_SITE_CONTENT, $this->domain->getId()),
            'exportSiteSlug' => $this->router->generate('front_personal_data_export', []),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'personalDataPage',
        ];
    }
}
