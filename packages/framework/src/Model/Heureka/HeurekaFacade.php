<?php

namespace Shopsys\FrameworkBundle\Model\Heureka;

use Exception;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Symfony\Bridge\Monolog\Logger;

class HeurekaFacade
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationFactory
     */
    protected $heurekaShopCertificationFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationService
     */
    protected $heurekaShopCertificationService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting
     */
    protected $heurekaSetting;

    public function __construct(
        Logger $logger,
        HeurekaShopCertificationFactory $heurekaShopCertificationFactory,
        HeurekaShopCertificationService $heurekaShopCertificationService,
        HeurekaSetting $heurekaSetting
    ) {
        $this->logger = $logger;
        $this->heurekaShopCertificationFactory = $heurekaShopCertificationFactory;
        $this->heurekaShopCertificationService = $heurekaShopCertificationService;
        $this->heurekaSetting = $heurekaSetting;
    }

    public function sendOrderInfo(Order $order)
    {
        try {
            $heurekaShopCertification = $this->heurekaShopCertificationFactory->create($order);
            $heurekaShopCertification->logOrder();
        } catch (\Shopsys\FrameworkBundle\Model\Heureka\Exception\LocaleNotSupportedException $ex) {
            $this->logError($ex, $order);
        } catch (\Heureka\ShopCertification\Exception $ex) {
            $this->logError($ex, $order);
        }
    }

    /**
     * @return bool
     */
    public function isHeurekaShopCertificationActivated($domainId)
    {
        return $this->heurekaSetting->isHeurekaShopCertificationActivated($domainId);
    }

    /**
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
        return $this->heurekaShopCertificationService->isDomainLocaleSupported($locale);
    }

    /**
     * @param string $locale
     * @return string|null
     */
    public function getServerNameByLocale($locale)
    {
        return $this->heurekaShopCertificationService->getServerNameByLocale($locale);
    }

    protected function logError(Exception $ex, Order $order)
    {
        $message = 'Sending order (ID = "' . $order->getId() . '") to Heureka failed - ' . get_class($ex) . ': ' . $ex->getMessage();
        $this->logger->error($message, ['exceptionFullInfo' => (string)$ex]);
    }
}
