<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Doctrine\DBAL\Driver\Middleware\AbstractStatementMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Override;

final class QueryCountingStatement extends AbstractStatementMiddleware
{
    public function __construct(
        Statement $statement,
        private readonly QueryCountingMiddleware $middleware,
    ) {
        parent::__construct($statement);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute($params = null): Result
    {
        $this->middleware->incrementQueryCount();

        return parent::execute($params);
    }
}
