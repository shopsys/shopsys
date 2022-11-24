<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Rector;

use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\UnionTypeHelper;

class RectorUnionTypeHelper
{
    /**
     * @param \PHPStan\Type\Type $inferedType
     * @return \PHPStan\Type\Type
     */
    public static function optimizeUnionType(Type $inferedType): Type
    {
        if ($inferedType instanceof UnionType) {
            /** @var \PHPStan\Type\Type[] $optimizedReturnTypes */
            $optimizedReturnTypes = [];
            $sortedTypes = array_reverse(UnionTypeHelper::sortTypes($inferedType->getTypes()));
            foreach ($sortedTypes as $type) {
                foreach ($optimizedReturnTypes as $optimizedReturnType) {
                    if ($optimizedReturnType->isSuperTypeOf($type)->yes()) {
                        continue 2;
                    }
                }

                $optimizedReturnTypes[] = $type;
            }
            $optimizedReturnTypes = array_filter($inferedType->getTypes(), static function (Type $type) use ($optimizedReturnTypes): bool {
                return in_array($type, $optimizedReturnTypes, true);
            });
            $optimizedReturnTypes = array_values($optimizedReturnTypes);

            if (count($optimizedReturnTypes) > 1) {
                $inferedType = new UnionType($optimizedReturnTypes);
            } else {
                $inferedType = array_pop($optimizedReturnTypes);
            }
        }

        return $inferedType;
    }
}
