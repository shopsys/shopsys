<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Heureka;

use Shopsys\FrameworkBundle\Model\Order\Order;

class HeurekaFacade
{
    public function __construct(
        protected readonly HeurekaShopCertificationFactory $heurekaShopCertificationFactory,
        protected readonly HeurekaShopCertificationLocaleHelper $heurekaShopCertificationLocaleHelper,
        protected readonly HeurekaSetting $heurekaSetting,
    ) {
    }

    public function sendOrderInfo(Order $order): void
    {
        $heurekaShopCertification = $this->heurekaShopCertificationFactory->create($order);
        $heurekaShopCertification->logOrder();
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isHeurekaShopCertificationActivated($domainId)
    {
        return $this->heurekaSetting->isHeurekaShopCertificationActivated($domainId);
    }

    /**
     * @param int $domainId
     * @return bool
     */
    public function isHeurekaWidgetActivated($domainId)
    {
        return $this->heurekaSetting->isHeurekaWidgetActivated($domainId);
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function isDomainLocaleSupported($locale)
    {
        return $this->heurekaShopCertificationLocaleHelper->isDomainLocaleSupported($locale);
    }

    /**
     * @param string $locale
     * @return string|null
     */
    public function getServerNameByLocale($locale)
    {
        return $this->heurekaShopCertificationLocaleHelper->getServerNameByLocale($locale);
    }
}
