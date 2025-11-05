<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Doctrine\Util;

use Stringable;

final class ObjectNameHelper
{
    public static function getObjectName(object $object): string
    {
        if ($object instanceof Stringable) {
            return (string)$object;
        }

        $className = get_class($object);

        return sprintf('%s@%s', $className, spl_object_hash($object));
    }
}
