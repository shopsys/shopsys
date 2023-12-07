<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\DBAL\Logging\SQLLogger;

class EchoSqlLogger implements SQLLogger
{

    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        d($sql);
        d($params);
    }

    public function stopQuery()
    {
        // TODO: Implement stopQuery() method.
    }
}
