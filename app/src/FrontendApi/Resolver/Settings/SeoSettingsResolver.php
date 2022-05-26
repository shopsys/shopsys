<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Settings;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;

class SeoSettingsResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade
     */
    protected SeoSettingFacade $seoSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade $seoSettingFacade
     */
    public function __construct(
        Domain $domain,
        SeoSettingFacade $seoSettingFacade
    ) {
        $this->seoSettingFacade = $seoSettingFacade;
        $this->domain = $domain;
    }

    /**
     * @return array{title: string, titleAddOn: string, metaDescription: string}
     */
    public function resolve(): array
    {
        return [
            'title' => $this->seoSettingFacade->getTitleMainPage($this->domain->getId()),
            'titleAddOn' => $this->seoSettingFacade->getTitleAddOn($this->domain->getId()),
            'metaDescription' => $this->seoSettingFacade->getDescriptionMainPage($this->domain->getId()),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'seoSettings'];
    }
}
