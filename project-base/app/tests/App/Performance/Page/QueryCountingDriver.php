<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use Override;

final class QueryCountingDriver extends AbstractDriverMiddleware
{
    public function __construct(
        Driver $driver,
        private readonly QueryCountingMiddleware $middleware,
    ) {
        parent::__construct($driver);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function connect(array $params): Connection
    {
        return new QueryCountingConnection(
            parent::connect($params),
            $this->middleware,
        );
    }
}
