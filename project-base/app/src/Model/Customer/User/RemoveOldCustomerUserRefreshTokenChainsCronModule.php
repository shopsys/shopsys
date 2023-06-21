<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class RemoveOldCustomerUserRefreshTokenChainsCronModule implements SimpleCronModuleInterface
{
    private Logger $logger;

    /**
     * @param \App\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository
     */
    public function __construct(
        private CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository,
    ) {
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function run(): void
    {
        $this->logger->info('Removing expired customer refresh token chains');
        $this->customerUserRefreshTokenChainRepository->removeOldCustomerRefreshTokenChains();
        $this->logger->info('Customer refresh token chains removed');
    }
}
