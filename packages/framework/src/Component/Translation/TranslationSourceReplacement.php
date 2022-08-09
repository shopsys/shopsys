<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

class TranslationSourceReplacement
{
    /**
     * @var string
     */
    protected $oldSource;

    /**
     * @var string
     */
    protected $newSource;

    /**
     * @var string
     */
    protected $domain;

    /**
     * @var string[]
     */
    protected $sourceFileReferences;

    /**
     * @param string $oldSource
     * @param string $newSource
     * @param string $domain
     * @param string[] $sourceFileReferences
     */
    public function __construct(string $oldSource, string $newSource, string $domain, array $sourceFileReferences)
    {
        $this->oldSource = $oldSource;
        $this->newSource = $newSource;
        $this->domain = $domain;
        $this->sourceFileReferences = $sourceFileReferences;
    }

    /**
     * @return string
     */
    public function getOldSource(): string
    {
        return $this->oldSource;
    }

    /**
     * @return string
     */
    public function getNewSource(): string
    {
        return $this->newSource;
    }

    /**
     * @return string
     */
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

    /**
     * @param string $sourceFilePath
     * @return int
     */
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

    /**
     * @param string $sourceFilePath
     * @return bool
     */
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

    /**
     * @param string $sourceFileReference
     * @return string
     */
    protected function extractSourceFilePathFromReference(string $sourceFileReference): string
    {
        return explode(':', $sourceFileReference)[0];
    }

    /**
     * @param string $sourceFileReference
     * @return int|null
     */
    protected function extractSourceFileLineFromReference(string $sourceFileReference): ?int
    {
        $parts = explode(':', $sourceFileReference);

        return count($parts) > 1 && is_numeric($parts[1]) ? (int)$parts[1] : null;
    }
}
