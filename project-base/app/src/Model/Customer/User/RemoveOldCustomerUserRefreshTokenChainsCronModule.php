<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class RemoveOldCustomerUserRefreshTokenChainsCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private Logger $logger;

    /**
     * @var \App\Model\Customer\User\CustomerUserRefreshTokenChainRepository
     */
    private CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository;

    /**
     * @param \App\Model\Customer\User\CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository
     */
    public function __construct(CustomerUserRefreshTokenChainRepository $customerUserRefreshTokenChainRepository)
    {
        $this->customerUserRefreshTokenChainRepository = $customerUserRefreshTokenChainRepository;
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
