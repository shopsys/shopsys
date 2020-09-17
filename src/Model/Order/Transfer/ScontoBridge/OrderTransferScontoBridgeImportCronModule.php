<?php

declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class OrderTransferScontoBridgeImportCronModule implements SimpleCronModuleInterface
{
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        // TODO: Implement run() method.
    }
}
