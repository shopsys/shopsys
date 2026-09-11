<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

class ClassExtensionRegistry
{
    /**
     * @param array<class-string, class-string> $classExtensionMap
     */
    public function __construct(
        protected readonly array $classExtensionMap,
    ) {
    }

    /**
     * @return array<class-string, class-string>
     */
    public function getClassExtensionMap(): array
    {
        return $this->classExtensionMap;
    }
}
