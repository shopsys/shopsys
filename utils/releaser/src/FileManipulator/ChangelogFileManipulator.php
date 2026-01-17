<?php

declare(strict_types=1);

namespace Shopsys\Releaser\FileManipulator;

use Nette\Utils\Strings;

final class ChangelogFileManipulator
{
    public function updateReleaseDateOfCurrentReleaseToToday(
        string $fileContent,
        string $currentReleaseHeadlinePattern,
        string $todayInString,
    ): string {
        return Strings::replace(
            $fileContent,
            $currentReleaseHeadlinePattern,
            function ($match) use ($todayInString) {
                return str_replace($match[1], $todayInString, $match[0]);
            },
        );
    }
}
