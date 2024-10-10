<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FilesProvider;

use Nette\Utils\Json;

class PackageNamesProvider
{
    /**
     * @param \Shopsys\Releaser\FilesProvider\ComposerJsonFilesProvider $composerJsonFilesProvider
     */
    public function __construct(
        private readonly ComposerJsonFilesProvider $composerJsonFilesProvider,
    ) {
    }

    /**
     * @return string[] Package names with vendor prefix (e.g. "shopsys/framework")
     */
    public function provide(): array
    {
        $packageNames = [];

        foreach ($this->composerJsonFilesProvider->provideExcludingMonorepoComposerJson() as $composerFileInfo) {
            $jsonContent = Json::decode($composerFileInfo->getContents(), Json::FORCE_ARRAY);

            $packageNames[] = $jsonContent['name'];
        }

        return $packageNames;
    }
}
