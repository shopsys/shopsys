<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

class TranslationSourceReplacement
{
    /**
     * @var string
     */
    private $oldSource;

    /**
     * @var string
     */
    private $newSource;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var string[]
     */
    private $sourceFileReferences;

    /**
     * @param string[] $sourceFileReferences
     */
    public function __construct(string $oldSource, string $newSource, string $domain, array $sourceFileReferences)
    {
        $this->oldSource = $oldSource;
        $this->newSource = $newSource;
        $this->domain = $domain;
        $this->sourceFileReferences = $sourceFileReferences;
    }

    public function getOldSource(): string
    {
        return $this->oldSource;
    }

    public function getNewSource(): string
    {
        return $this->newSource;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * Paths relative to any of directories that are scanned for translations
     * @return string[]
     */
    public function getSourceFilePaths(): array
    {
        $sourceFilePaths = [];
        foreach ($this->sourceFileReferences as $sourceFileReference) {
            $sourceFilePaths[] = $this->extractSourceFilePathFromReference($sourceFileReference);
        }

        return array_unique($sourceFilePaths);
    }

    public function getExpectedReplacementsCountForSourceFilePath(string $sourceFilePath): int
    {
        $expectedReplacementsCount = 0;
        foreach ($this->sourceFileReferences as $sourceFileReference) {
            if ($this->extractSourceFilePathFromReference($sourceFileReference) === $sourceFilePath) {
                $expectedReplacementsCount++;
            }
        }

        return $expectedReplacementsCount;
    }

    public function isExpectedReplacementsCountExact(string $sourceFilePath): bool
    {
        foreach ($this->sourceFileReferences as $sourceFileReference) {
            if ($this->extractSourceFilePathFromReference($sourceFileReference) === $sourceFilePath) {
                if ($this->extractSourceFileLineFromReference($sourceFileReference) === null) {
                    return false;
                }
            }
        }

        return true;
    }

    private function extractSourceFilePathFromReference(string $sourceFileReference): string
    {
        return explode(':', $sourceFileReference)[0];
    }

    private function extractSourceFileLineFromReference(string $sourceFileReference): ?int
    {
        $parts = explode(':', $sourceFileReference);

        return count($parts) > 1 && is_numeric($parts[1]) ? (int)$parts[1] : null;
    }
}
