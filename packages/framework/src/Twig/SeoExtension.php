<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_SimpleFunction;

class SeoExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Seo\SeoSettingFacade
     */
    private $seoSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(
        ContainerInterface $container,
        SeoSettingFacade $seoSettingFacade,
        Domain $domain
    ) {
        $this->container = $container;
        $this->seoSettingFacade = $seoSettingFacade;
        $this->domain = $domain;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('getSeoTitleAddOn', [$this, 'getSeoTitleAddOn']),
            new Twig_SimpleFunction('getSeoMetaDescription', [$this, 'getSeoMetaDescription']),
        ];
    }

    public function getName()
    {
        return 'seo';
    }

    public function getSeoTitleAddOn()
    {
        $currentDomainId = $this->domain->getId();
        return $this->seoSettingFacade->getTitleAddOn($currentDomainId);
    }

    public function getSeoMetaDescription()
    {
        $currentDomainId = $this->domain->getId();
        return $this->seoSettingFacade->getDescriptionMainPage($currentDomainId);
    }
}
