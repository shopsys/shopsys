<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

class AnnotationsReplacementsMap
{
    /**
     * @param string[] $classExtensionMap
     */
    public function __construct(protected readonly array $classExtensionMap)
    {
    }

    /**
     * @return string[]
     */
    public function getPatterns(): array
    {
        $patterns = [];

        foreach (array_keys($this->classExtensionMap) as $frameworkClass) {
            $patterns[] = '/\\\\' . preg_quote(ltrim($frameworkClass, '\\'), '/') . '(?!\w)/';
        }

        return $patterns;
    }

    /**
     * @return string[]
     */
    public function getReplacements(): array
    {
        $replacements = [];

        foreach (array_values($this->classExtensionMap) as $projectClass) {
            $replacements[] = '\\' . ltrim($projectClass, '\\');
        }

        return $replacements;
    }
}
