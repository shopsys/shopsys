<?php

namespace Shopsys\ShopBundle\Model\Order\Pohoda;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ImportPohodaOrderCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @var \Shopsys\ShopBundle\Model\Order\Pohoda\PohodaOrderRepository
     */
    private $pohodaOrderRepository;

    /**
     * @param \Shopsys\ShopBundle\Model\Order\Pohoda\PohodaOrderRepository $pohodaOrderRepository
     */
    public function __construct(PohodaOrderRepository $pohodaOrderRepository)
    {
        $this->pohodaOrderRepository = $pohodaOrderRepository;
    }

    /**
     * @inheritdoc
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $order = $this->pohodaOrderRepository->getOrderDataByOrderNumber('182100006');
        $this->logger->addInfo('Order processed ...');
    }
}
