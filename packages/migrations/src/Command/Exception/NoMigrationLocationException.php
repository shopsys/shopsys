<?php

declare(strict_types=1);

namespace Shopsys\MigrationBundle\Command\Exception;

use Exception;

class NoMigrationLocationException extends Exception
{
    public function __construct()
    {
        parent::__construct('There is no migration location available, check your "migrations_paths" setting in "doctrine_migrations.yaml" configuration');
    }
}
