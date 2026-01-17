<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Security;

use Monolog\Logger;
use Override;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class DeleteExpiredExchangeTokensCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    public function __construct(
        protected readonly LoginAsUserExchangeTokenFacade $loginAsUserExchangeTokenFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    #[Override]
    public function run(): void
    {
        $this->logger->info('Removing expired login as user exchange tokens');
        $this->loginAsUserExchangeTokenFacade->deleteAllExpired();
        $this->logger->info('Login as user exchange tokens removed');
    }
}
