<?php

namespace Shopsys\FrameworkBundle\Model\Heureka;

use Heureka\ShopCertification;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Order;

class HeurekaShopCertificationFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting $heurekaSetting
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationLocaleHelper $heurekaShopCertificationLocaleHelper
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly HeurekaSetting $heurekaSetting,
        protected readonly HeurekaShopCertificationLocaleHelper $heurekaShopCertificationLocaleHelper
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Heureka\ShopCertification
     */
    public function create(Order $order)
    {
        $domainConfig = $this->domain->getDomainConfigById($order->getDomainId());

        $languageId = $this->heurekaShopCertificationLocaleHelper->getLanguageIdByLocale($domainConfig->getLocale());
        $heurekaApiKey = $this->heurekaSetting->getApiKeyByDomainId($domainConfig->getId());
        $options = ['service' => $languageId];

        $heurekaShopCertification = new ShopCertification($heurekaApiKey, $options);
        $heurekaShopCertification->setOrderId($order->getId());
        $heurekaShopCertification->setEmail($order->getEmail());

        foreach ($order->getProductItems() as $item) {
            if ($item->hasProduct()) {
                $heurekaShopCertification->addProductItemId((string)$item->getProduct()->getId());
            }
        }

        return $heurekaShopCertification;
    }
}
