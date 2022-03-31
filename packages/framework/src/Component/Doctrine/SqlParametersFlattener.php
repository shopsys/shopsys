<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\DBAL\ExpandArrayParameters;
use Doctrine\DBAL\SQL\Parser;

class SqlParametersFlattener
{
    /**
     * inspired by @see \Doctrine\DBAL\Connection::expandArrayParameters()
     *
     * @param string $dql
     * @param array<string, mixed> $parameters
     * @return array
     */
    public static function flattenArrayParameters(string $dql, array $parameters): array
    {
        $parser = new Parser(false);
        $visitor = new ExpandArrayParameters($parameters, []);
        $parser->parse($dql, $visitor);

        return $visitor->getParameters();
    }
}
