<?php

declare(strict_types=1);

namespace Shopsys\McpBundle\Component\Database\Middleware\Driver;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Override;
use SensitiveParameter;

class McpSessionSettingsDriver extends AbstractDriverMiddleware
{
    public function __construct(
        Driver $wrappedDriver,
        protected readonly int $statementTimeoutMilliseconds,
        protected readonly int $lockTimeoutMilliseconds,
    ) {
        parent::__construct($wrappedDriver);
    }

    #[Override]
    public function connect(
        #[SensitiveParameter]
        array $params,
    ): Connection {
        $connection = parent::connect($params);

        $connection->exec('SET default_transaction_read_only TO on');
        $connection->exec(sprintf(
            'SET statement_timeout TO %s',
            $connection->quote(sprintf('%dms', $this->statementTimeoutMilliseconds)),
        ));
        $connection->exec(sprintf(
            'SET lock_timeout TO %s',
            $connection->quote(sprintf('%dms', $this->lockTimeoutMilliseconds)),
        ));

        return $connection;
    }
}
