<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Psr\Log\LoggerInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class RemoveOldCustomerUserRefreshTokenChainsCronModule implements SimpleCronModuleInterface
{
    private LoggerInterface $logger;

    /**
     * @param \App\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository
     */
    public function __construct(
        private CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository,
    ) {
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
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
