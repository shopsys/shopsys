<?php

namespace Shopsys\FrameworkBundle\Component\Translation;

class TranslationSourceReplacement
{
    protected string $oldSource;

    protected string $newSource;

    protected string $domain;

    /**
     * @param string $oldSource
     * @param string $newSource
     * @param string $domain
     * @param string[] $sourceFileReferences
     */
    public function __construct($oldSource, $newSource, $domain, protected readonly array $sourceFileReferences)
    {
        $this->oldSource = $oldSource;
        $this->newSource = $newSource;
        $this->domain = $domain;
    }

    /**
     * @return string
     */
    public function getOldSource()
    {
        return $this->oldSource;
    }

    /**
     * @return string
     */
    public function getNewSource()
    {
        return $this->newSource;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Paths relative to any of directories that are scanned for translations
     *
     * @return string[]
     */
    public function getSourceFilePaths()
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
    public function getExpectedReplacementsCountForSourceFilePath($sourceFilePath)
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
    public function isExpectedReplacementsCountExact($sourceFilePath)
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
    protected function extractSourceFilePathFromReference($sourceFileReference)
    {
        return explode(':', $sourceFileReference)[0];
    }

    /**
     * @param string $sourceFileReference
     * @return int|null
     */
    protected function extractSourceFileLineFromReference($sourceFileReference)
    {
        $parts = explode(':', $sourceFileReference);

        return count($parts) > 1 && is_numeric($parts[1]) ? (int)$parts[1] : null;
    }
}
