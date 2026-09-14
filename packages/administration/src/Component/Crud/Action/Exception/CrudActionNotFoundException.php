<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Action\Exception;

use InvalidArgumentException;

class CrudActionNotFoundException extends InvalidArgumentException
{
    /**
     * @param string[] $availableNames
     */
    public static function create(string $controllerClass, string $name, array $availableNames): self
    {
        return new self(sprintf(
            'CRUD controller "%s" has no action "%s". Available actions: %s.',
            $controllerClass,
            $name,
            implode(', ', $availableNames),
        ));
    }
}
