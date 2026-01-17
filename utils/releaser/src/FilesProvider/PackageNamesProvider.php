<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FilesProvider;

use Nette\Utils\Json;

class PackageNamesProvider
{
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
            $jsonContent = Json::decode($composerFileInfo->getContents(), true);

            $packageNames[] = $jsonContent['name'];
        }

        return $packageNames;
    }
}
