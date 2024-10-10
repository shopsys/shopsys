<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;

class ComposerJsonFileManipulator
{
    /**
     * @param \Symfony\Component\Finder\SplFileInfo[] $fileInfos
     * @param string[] $packageNames
     * @param string $version
     */
    public function setMutualDependenciesToVersion(
        array $fileInfos,
        array $packageNames,
        string $version,
    ): void {
        foreach ($fileInfos as $fileInfo) {
            $jsonContent = Json::decode($fileInfo->getContents(), Json::FORCE_ARRAY);

            foreach ($packageNames as $packageName) {
                $jsonContent = $this->replaceVersion($jsonContent, $packageName, $version);
            }

            $fileContent = Json::encode($jsonContent, Json::PRETTY) . PHP_EOL;
            FileSystem::write($fileInfo->getRealPath(), $fileContent);
        }
    }

    /**
     * @param array $jsonContent
     * @param string $packageName
     * @param string $requestedVersion
     * @return array
     */
    private function replaceVersion(array $jsonContent, string $packageName, string $requestedVersion): array
    {
        if (isset($jsonContent['require'][$packageName])) {
            $jsonContent['require'][$packageName] = $requestedVersion;
        }

        if (isset($jsonContent['require-dev'][$packageName])) {
            $jsonContent['require-dev'][$packageName] = $requestedVersion;
        }

        return $jsonContent;
    }
}
