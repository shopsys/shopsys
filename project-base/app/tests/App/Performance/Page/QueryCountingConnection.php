<?php

declare(strict_types=1);

namespace Tests\App\Performance\Page;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware\AbstractConnectionMiddleware;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Override;

final class QueryCountingConnection extends AbstractConnectionMiddleware
{
    public function __construct(
        Connection $connection,
        private readonly QueryCountingMiddleware $middleware,
    ) {
        parent::__construct($connection);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function prepare(string $sql): Statement
    {
        return new QueryCountingStatement(
            parent::prepare($sql),
            $this->middleware,
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function query(string $sql): Result
    {
        $this->middleware->incrementQueryCount();

        return parent::query($sql);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function exec(string $sql): int
    {
        $this->middleware->incrementQueryCount();

        return parent::exec($sql);
    }
}
