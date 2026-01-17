<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Enum;

use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;

class AbstractEnum
{
    /**
     * @return string[]
     */
    public function getAllCases(): array
    {
        $publicConstants = ReflectionHelper::getAllPublicClassConstants(static::class);

        return array_diff($publicConstants, $this->getUnusedConstants());
    }

    public function validateCase(string $case): void
    {
        if (!in_array($case, $this->getAllCases(), true)) {
            throw new InvalidEnumCaseException(static::class, $case);
        }
    }

    /**
     * @return string[]
     */
    protected function getUnusedConstants(): array
    {
        return [];
    }
}
