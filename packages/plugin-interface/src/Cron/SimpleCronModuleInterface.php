<?php

declare(strict_types=1);

namespace Shopsys\Plugin\Cron;

use Monolog\Logger;

/**
 * SimpleCronModuleInterface is the interface that all simple CRON modules must implement.
 *
 * In order for your CRON module to be run you must register it as a service tagged as "shopsys.cron"
 * with a "cron" tag attribute containing a standard cron expression (e.g. cron: "0,30 * * * *" for running every half hour).
 * Module is run every time the current system time matches the expression specified in cron.yaml.
 * The module should not take more than one minute to run. If you want to process longer
 * taking tasks @see \Shopsys\Plugin\Cron\IteratedCronModuleInterface.
 */
interface SimpleCronModuleInterface
{
    public function setLogger(Logger $logger): void;

    /**
     * This method is called to run the CRON module.
     */
    public function run(): void;
}
