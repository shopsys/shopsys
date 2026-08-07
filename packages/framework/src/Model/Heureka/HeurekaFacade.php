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

    public function isHeurekaShopCertificationActivated(int $domainId): bool
    {
        return $this->heurekaSetting->isHeurekaShopCertificationActivated($domainId);
    }

    public function isDomainLocaleSupported(string $locale): bool
    {
        return $this->heurekaShopCertificationLocaleHelper->isDomainLocaleSupported($locale);
    }

    public function getServerNameByLocale(string $locale): ?string
    {
        return $this->heurekaShopCertificationLocaleHelper->getServerNameByLocale($locale);
    }
}
