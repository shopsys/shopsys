<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

class AnnotationsReplacer
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap
     */
    protected $annotationsReplacementsMap;

    /**
     * @param \Shopsys\FrameworkBundle\Component\ClassExtension\AnnotationsReplacementsMap $annotationsReplacementsMap
     */
    public function __construct(AnnotationsReplacementsMap $annotationsReplacementsMap)
    {
        $this->annotationsReplacementsMap = $annotationsReplacementsMap;
    }

    /**
     * @param string $string
     * @return string
     */
    public function replaceIn(string $string): string
    {
        return preg_replace(
            $this->annotationsReplacementsMap->getPatterns(),
            $this->annotationsReplacementsMap->getReplacements(),
            $string
        );
    }
}
