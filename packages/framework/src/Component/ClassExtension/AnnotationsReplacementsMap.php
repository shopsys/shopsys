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

    /**
     * This method assumes that {@see AnnotationsReplacementsMap::getPatterns()} doesn't use modifiers after delimiter
     *
     * @return string
     */
    public function getPatternForAny(): string
    {
        $patternsWithoutDelimiters = [];

        foreach ($this->getPatterns() as $pattern) {
            $patternsWithoutDelimiters[] = substr($pattern, 1, -1);
        }

        return '/' . implode('|', $patternsWithoutDelimiters) . '/';
    }
}
