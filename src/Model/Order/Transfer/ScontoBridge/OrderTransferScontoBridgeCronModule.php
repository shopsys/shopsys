<?php
declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class OrderTransferScontoBridgeCronModule implements SimpleCronModuleInterface
{
    /**
     * @var OrderTransferScontoBridgeFacade
     */
    private OrderTransferScontoBridgeFacade $orderTransferScontoBridgeFacade;

    public function __construct(OrderTransferScontoBridgeFacade $orderTransferScontoBridgeFacade)
    {
        $this->orderTransferScontoBridgeFacade = $orderTransferScontoBridgeFacade;
    }

    public function run()
    {
        $this->orderTransferScontoBridgeFacade->runTransfer();
    }

    public function setLogger(Logger $logger)
    {
    }
}
