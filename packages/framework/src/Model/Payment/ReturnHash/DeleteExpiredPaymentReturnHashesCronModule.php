<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\ReturnHash;

use Monolog\Logger;
use Override;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class DeleteExpiredPaymentReturnHashesCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    public function __construct(
        protected readonly PaymentReturnHashFacade $paymentReturnHashFacade,
    ) {
    }

    #[Override]
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    #[Override]
    public function run(): void
    {
        $this->logger->info('Removing expired payment return hashes');
        $this->paymentReturnHashFacade->deleteAllExpired();
        $this->logger->info('Expired payment return hashes removed');
    }
}
