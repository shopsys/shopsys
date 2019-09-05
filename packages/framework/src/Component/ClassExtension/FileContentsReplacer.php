<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\ClassExtension;

class FileContentsReplacer
{
    /**
     * @param string $fileName
     * @param string $search
     * @param string $replace
     */
    public function replaceInFile(string $fileName, string $search, string $replace): void
    {
        $fileContent = file_get_contents($fileName);
        $replacedContent = str_replace($search, $replace, $fileContent);
        file_put_contents($fileName, $replacedContent);
    }
}
