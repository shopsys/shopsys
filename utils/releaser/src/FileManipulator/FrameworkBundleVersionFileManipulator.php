<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\Strings;
use PharIo\Version\Version;

final class FrameworkBundleVersionFileManipulator
{
    /**
     * @var string
     */
    public const string FRAMEWORK_BUNDLE_VERSION_FILE_PATH = '/packages/framework/src/ShopsysFrameworkBundle.php';

    /**
     * @var string
     */
    private const string FRAMEWORK_BUNDLE_VERSION_PATTERN = "/public const VERSION = '(.+)';/";

    /**
     * @param string $content
     * @param \PharIo\Version\Version $version
     * @return string
     */
    public function updateFrameworkBundleVersion(string $content, Version $version): string
    {
        return Strings::replace(
            $content,
            self::FRAMEWORK_BUNDLE_VERSION_PATTERN,
            function ($match) use ($version) {
                return str_replace($match[1], $version->getVersionString(), $match[0]);
            },
        );
    }
}
