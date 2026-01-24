<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Translation;

class TranslationSourceReplacement
{
    /**
     * @param string[] $sourceFileReferences
     */
    public function __construct(
        protected string $oldSource,
        protected string $newSource,
        protected string $domain,
        protected readonly array $sourceFileReferences,
    ) {
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
     *
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

    protected function extractSourceFilePathFromReference(string $sourceFileReference): string
    {
        return explode(':', $sourceFileReference)[0];
    }

    protected function extractSourceFileLineFromReference(string $sourceFileReference): ?int
    {
        $parts = explode(':', $sourceFileReference);

        return count($parts) > 1 && is_numeric($parts[1]) ? (int)$parts[1] : null;
    }
}
