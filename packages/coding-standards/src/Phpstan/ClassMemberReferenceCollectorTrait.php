<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

trait ClassMemberReferenceCollectorTrait
{
    /**
     * @var array<string, array<string, true>>
     */
    protected array $referencedMembersByClass = [];

    /**
     * @param string[] $classMemberPatterns
     */
    protected function collectClassMemberReferences(string $content, array $classMemberPatterns): void
    {
        foreach ($classMemberPatterns as $classMemberPattern) {
            if (preg_match_all($classMemberPattern, $content, $matches, PREG_SET_ORDER) === false) {
                continue;
            }

            foreach ($matches as $match) {
                $className = ltrim(str_replace('\\\\', '\\', $match[1]), '\\');

                if (!str_contains($className, '\\')) {
                    continue;
                }

                $this->referencedMembersByClass[$className][$match[2]] = true;
            }
        }
    }

    protected function isMemberReferenced(string $className, string $memberName): bool
    {
        return isset($this->referencedMembersByClass[$className][$memberName]);
    }
}
