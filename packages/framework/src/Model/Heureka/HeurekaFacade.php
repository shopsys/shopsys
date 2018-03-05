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
    private $logger;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationFactory
     */
    private $heurekaShopCertificationFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationService
     */
    private $heurekaShopCertificationService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting
     */
    private $heurekaSetting;

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationFactory $heurekaShopCertificationFactory
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaShopCertificationService $heurekaShopCertificationService
     * @param \Shopsys\FrameworkBundle\Model\Heureka\HeurekaSetting $heurekaSetting
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
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

    /**
     * @param \Exception $ex
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     */
    private function logError(Exception $ex, Order $order)
    {
        $message = 'Sending order (ID = "' . $order->getId() . '") to Heureka failed - ' . get_class($ex) . ': ' . $ex->getMessage();
        $this->logger->error($message, ['exceptionFullInfo' => (string)$ex]);
    }
}
