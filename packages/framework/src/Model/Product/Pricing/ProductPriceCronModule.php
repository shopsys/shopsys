<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ProductPriceCronModule implements IteratedCronModuleInterface
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @inheritdoc
     */
    public function setLogger(Logger $logger)
    {
        //remove class
        $this->logger = $logger;
    }

    public function sleep()
    {
    }

    public function wakeUp()
    {
    }

    /**
     * @inheritdoc
     */
    public function iterate()
    {
    }
}
