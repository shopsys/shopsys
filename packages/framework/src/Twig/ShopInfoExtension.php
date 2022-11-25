<?php

namespace Shopsys\FrameworkBundle\Twig;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\ShopInfo\ShopInfoSettingFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ShopInfoExtension extends AbstractExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\ShopInfo\ShopInfoSettingFacade
     */
    protected $shopInfoSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\ShopInfo\ShopInfoSettingFacade $shopInfoSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ShopInfoSettingFacade $shopInfoSettingFacade,
        Domain $domain
    ) {
        $this->shopInfoSettingFacade = $shopInfoSettingFacade;
        $this->domain = $domain;
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getShopInfoPhoneNumber', [$this, 'getPhoneNumber']),
            new TwigFunction('getShopInfoEmail', [$this, 'getEmail']),
            new TwigFunction('getShopInfoPhoneHours', [$this, 'getPhoneHours']),
        ];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected function getDomain(): Domain
    {
        // Twig extensions are loaded during assetic:dump command,
        // so they cannot be dependent on Domain service
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'shopInfo';
    }

    /**
     * @return string|null
     */
    public function getPhoneNumber(): ?string
    {
        $currentDomainId = $this->getDomain()->getId();

        return $this->shopInfoSettingFacade->getPhoneNumber($currentDomainId);
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        $currentDomainId = $this->getDomain()->getId();

        return $this->shopInfoSettingFacade->getEmail($currentDomainId);
    }

    /**
     * @return string|null
     */
    public function getPhoneHours(): ?string
    {
        $currentDomainId = $this->getDomain()->getId();

        return $this->shopInfoSettingFacade->getPhoneHours($currentDomainId);
    }
}
