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

    /**
     * @param \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityRelationTypeEnum $relationType
     * @return \Shopsys\FrameworkBundle\Maker\EntityConfig\EntityRelationTypeEnum
     */
    public static function getInverseType(self $relationType): EntityRelationTypeEnum
    {
        return match ($relationType) {
            self::MANY_TO_ONE => self::ONE_TO_MANY,
            self::ONE_TO_MANY => self::MANY_TO_ONE,
            self::MANY_TO_MANY => self::MANY_TO_MANY,
        };
    }
}
