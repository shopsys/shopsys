<?php

declare(strict_types=1);

namespace Doctrine\Common\Annotations;

class DocParser
{
    /**
     * @param array<string, string> $imports
     */
    public function setImports(array $imports): void
    {
    }

    public function setIgnoreNotImportedAnnotations(bool $bool): void
    {
    }

    /**
     * @return array<mixed>
     */
    public function parse(string $input, string $context = ''): array
    {
        return [];
    }
}
