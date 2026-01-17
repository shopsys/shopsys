<?php

declare(strict_types=1);

namespace Shopsys\MakerBundle\Utils;

use Symfony\Bundle\MakerBundle\Str;

final class NamingHelper
{
    public static function convertEntityNameToTableName(string $entityName): string
    {
        return Str::asSnakeCase(Str::singularCamelCaseToPluralCamelCase(Str::getShortClassName($entityName)));
    }

    public static function getJoinTableName(string $entityName, string $relatedEntityName): string
    {
        return sprintf('%s_%s', Str::asSnakeCase(Str::getShortClassName($entityName)), self::convertEntityNameToTableName(Str::getShortClassName($relatedEntityName)));
    }
}
