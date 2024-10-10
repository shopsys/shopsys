<?php

declare(strict_types=1);

namespace Shopsys\Releaser;

use Nette\Utils\Strings;

final class IntervalEvaluator
{
    /**
     * @var string
     */
    private const string EXACT_VERSION_PATTERN = '#^(v|[0-9])#';

    /**
     * @param string $version
     * @return bool
     */
    public function isClosedInterval(string $version): bool
    {
        $version = $this->normalizeVersion($version);

        // e.g. "3.4.15|3.4.16"
        $intervals = Strings::split($version, '#\|{1,2}#');

        $isClosedInterval = true;

        foreach ($intervals as $singleInterval) {
            $singleInterval = trim($singleInterval);

            if (!Strings::contains($singleInterval, ',')) { // one sided interval|version?
                if (!Strings::match($singleInterval, self::EXACT_VERSION_PATTERN)) {
                    // definitely opened
                    return false;
                }
            } else {
                if (Strings::match($singleInterval, '#>(.*?),<(.*?)#')) {
                    // is closed probably
                    $isClosedInterval = true;
                }

                if (Strings::match($singleInterval, '#<(.*?),>(.*?)#')) {
                    $isClosedInterval = false;
                }
            }
        }

        return $isClosedInterval;
    }

    /**
     * @param string $version
     * @return string
     */
    private function normalizeVersion(string $version): string
    {
        // remove spaces
        return Strings::replace($version, '#\s#', '');
    }
}
