<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker\Utils;

use Symfony\Bundle\MakerBundle\Str;

final class NamingHelper
{
    /**
     * @param string $entityName
     * @return string
     */
    public static function convertEntityNameToTableName(string $entityName): string
    {
        return Str::asSnakeCase(Str::singularCamelCaseToPluralCamelCase($entityName));
    }

    /**
     * @param string $entityName
     * @param string $relatedEntityName
     * @return string
     */
    public static function getJoinTableName(string $entityName, string $relatedEntityName): string
    {
        return sprintf('%s_%s', Str::asSnakeCase(Str::getShortClassName($entityName)), self::convertEntityNameToTableName(Str::getShortClassName($relatedEntityName)));
    }
}
