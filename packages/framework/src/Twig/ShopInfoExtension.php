<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\ShopInfo\ShopInfoSettingFacade;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig_SimpleFunction;

class ShopInfoExtension extends \Twig_Extension
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Shopsys\FrameworkBundle\Model\ShopInfo\ShopInfoSettingFacade
     */
    private $shopInfoSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    public function __construct(
        ContainerInterface $container,
        ShopInfoSettingFacade $shopInfoSettingFacade,
        Domain $domain
    ) {
        $this->container = $container;
        $this->shopInfoSettingFacade = $shopInfoSettingFacade;
        $this->domain = $domain;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction('getShopInfoPhoneNumber', [$this, 'getPhoneNumber']),
            new Twig_SimpleFunction('getShopInfoEmail', [$this, 'getEmail']),
            new Twig_SimpleFunction('getShopInfoPhoneHours', [$this, 'getPhoneHours']),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private function getDomain()
    {
        // Twig extensions are loaded during assetic:dump command,
        // so they cannot be dependent on Domain service
        return $this->domain;
    }

    public function getName()
    {
        return 'shopInfo';
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber()
    {
        $currentDomainId = $this->getDomain()->getId();

        return $this->shopInfoSettingFacade->getPhoneNumber($currentDomainId);
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        $currentDomainId = $this->getDomain()->getId();

        return $this->shopInfoSettingFacade->getEmail($currentDomainId);
    }

    /**
     * @return string|null
     */
    public function getPhoneHours()
    {
        $currentDomainId = $this->getDomain()->getId();

        return $this->shopInfoSettingFacade->getPhoneHours($currentDomainId);
    }
}
