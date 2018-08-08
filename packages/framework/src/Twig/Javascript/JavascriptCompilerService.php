<?php

namespace Shopsys\FrameworkBundle\Twig\Javascript;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompiler;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Filesystem\Filesystem;

class JavascriptCompilerService
{
    const NOT_COMPILED_FOLDER = '/plugins/';

    /**
     * @var string
     */
    private $webPath;

    /**
     * @var string
     */
    private $jsUrlPrefix;

    /**
     * @var string[]
     */
    private $jsSourcePaths;

    /**
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Javascript\Compiler\JsCompiler
     */
    private $jsCompiler;

    /**
     * @var array
     */
    private $javascriptLinks = [];

    /**
     * @var \Symfony\Component\Asset\Packages
     */
    private $assetPackages;

    public function __construct(
        string $webPath,
        array $jsSourcePaths,
        string $jsUrlPrefix,
        Filesystem $filesystem,
        Domain $domain,
        JsCompiler $jsCompiler,
        Packages $assetPackages
    ) {
        $this->webPath = $webPath;
        $this->jsSourcePaths = $jsSourcePaths;
        $this->jsUrlPrefix = $jsUrlPrefix;
        $this->filesystem = $filesystem;
        $this->domain = $domain;
        $this->jsCompiler = $jsCompiler;
        $this->assetPackages = $assetPackages;
    }

    /**
     * @param string[] $javascripts
     * @return string[] URLs of compiled JS files
     */
    public function generateCompiledFiles(array $javascripts): array
    {
        $this->javascriptLinks = [];

        foreach ($javascripts as $javascript) {
            $this->process($javascript);
        }

        return array_unique($this->javascriptLinks);
    }

    private function process(string $javascript): void
    {
        foreach ($this->jsSourcePaths as $jsSourcePath) {
            if ($this->tryToProcessJavascriptFile($jsSourcePath, $javascript)) {
                return;
            }
        }

        foreach ($this->jsSourcePaths as $jsSourcePath) {
            if ($this->tryToProcessJavascriptDirectoryMask($jsSourcePath, $javascript)) {
                return;
            }
        }

        $this->processExternalJavascript($javascript);
    }

    private function tryToProcessJavascriptFile(string $jsSourcePath, string $javascript): bool
    {
        $sourcePath = $jsSourcePath . '/' . $javascript;
        $relativeTargetPath = $this->getRelativeTargetPath($javascript);

        if ($relativeTargetPath === null) {
            return false;
        }

        if (is_file($sourcePath)) {
            $lastModified = filemtime($sourcePath);
            $relativeTargetPathWithTimestamp = $this->getPathWithTimestamp($relativeTargetPath, $lastModified);
            $this->compileJavascriptFile($sourcePath, $relativeTargetPathWithTimestamp);
            $this->javascriptLinks[] = $this->assetPackages->getUrl($relativeTargetPathWithTimestamp);
            return true;
        }

        return false;
    }

    private function getPathWithTimestamp(string $relativePath, string $timestamp): string
    {
        $version = '-v' . $timestamp;

        return substr_replace($relativePath, $version, strrpos($relativePath, '.'), 0);
    }

    private function getRelativeTargetPath(string $javascript): string
    {
        $relativeTargetPath = null;
        if (strpos($javascript, 'admin/') === 0 || strpos($javascript, 'frontend/') === 0 || strpos($javascript, 'common/') === 0) {
            $relativeTargetPath = substr($this->jsUrlPrefix, 1) . $javascript;
            if (strpos($relativeTargetPath, '/') === 0) {
                $relativeTargetPath = substr($relativeTargetPath, 1);
            }

            $relativeTargetPath = str_replace('/scripts/', '/scripts/' . $this->domain->getLocale() . '/', $relativeTargetPath);
        }

        return $relativeTargetPath;
    }

    private function compileJavascriptFile(string $sourceFilename, string $relativeTargetPath): void
    {
        $compiledFilename = $this->webPath . '/' . $relativeTargetPath;

        if (!$this->isCompiledFileFresh($compiledFilename, $sourceFilename)) {
            $content = file_get_contents($sourceFilename);

            if (strpos($sourceFilename, self::NOT_COMPILED_FOLDER) === false) {
                $newContent = $this->jsCompiler->compile($content);
            } else {
                $newContent = $content;
            }

            $this->filesystem->mkdir(dirname($compiledFilename));
            $this->filesystem->dumpFile($compiledFilename, $newContent);
        }
    }

    private function isCompiledFileFresh(string $compiledFilename, string $sourceFilename): bool
    {
        if (is_file($compiledFilename) && parse_url($sourceFilename, PHP_URL_HOST) === null) {
            $isCompiledFileFresh = filemtime($sourceFilename) < filemtime($compiledFilename);
        } else {
            $isCompiledFileFresh = false;
        }
        return $isCompiledFileFresh;
    }

    private function tryToProcessJavascriptDirectoryMask(string $jsSourcePath, string $directoryMask): bool
    {
        $parts = explode('/', $directoryMask);
        $mask = array_pop($parts);
        $path = implode('/', $parts);

        if (!$this->isMaskValid($mask) || !is_dir($jsSourcePath . '/' . $path)) {
            return false;
        }

        $filenameMask = $mask === '' ? '*' : $mask;
        return $this->processJavascriptByMask($jsSourcePath, $path, $filenameMask);
    }

    private function processJavascriptByMask(string $jsSourcePath, string $path, string $filenameMask): bool
    {
        $filepaths = (array)glob($jsSourcePath . '/' . $path . '/' . $filenameMask);
        foreach ($filepaths as $filepath) {
            $javascript = str_replace($jsSourcePath . '/', '', $filepath);
            $this->tryToProcessJavascriptFile($jsSourcePath, $javascript);
        }

        return true;
    }

    private function isMaskValid(string $filenameMask): bool
    {
        return $filenameMask === '' || strpos($filenameMask, '*') !== false;
    }

    private function processExternalJavascript(string $javascriptUrl): void
    {
        $this->javascriptLinks[] = $this->assetPackages->getUrl($javascriptUrl);
    }
}
