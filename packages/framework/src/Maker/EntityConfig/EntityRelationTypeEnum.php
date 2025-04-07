<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Maker\EntityConfig;

enum EntityRelationTypeEnum: string
{
    case MANY_TO_ONE = 'ManyToOne';
    case ONE_TO_MANY = 'OneToMany';
    case MANY_TO_MANY = 'ManyToMany';

    /**
     * @return string[]
     */
    public static function getAllValues(): array
    {
        return array_map(static fn (EntityRelationTypeEnum $case) => $case->value, self::cases());
    }
}
