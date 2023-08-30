<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Migrations\Exception;

use Exception;

class ContainerNotSetException extends Exception
{
    /**
     * @param string $className
     */
    public function __construct(string $className)
    {
        parent::__construct(
            sprintf(
                'Migrations such as %s using MultidomainMigrationTrait must also implement ContainerAwareInterface.',
                $className,
            ),
        );
    }
}
