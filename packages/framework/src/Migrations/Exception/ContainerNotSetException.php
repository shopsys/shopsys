<?php

namespace Shopsys\FrameworkBundle\Migrations\Exception;

use Exception;

class ContainerNotSetException extends Exception
{
    public function __construct(string $className)
    {
        parent::__construct(
            sprintf(
                'Migrations such as %s using MultidomainMigrationTrait must also implement ContainerAwareInterface.',
                $className,
            )
        );
    }
}
