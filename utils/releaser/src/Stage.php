<?php

declare(strict_types=1);

namespace Shopsys\Releaser;

final class Stage
{
    /**
     * @var string
     */
    public const string RELEASE_CANDIDATE = 'release-candidate';

    /**
     * @var string
     */
    public const string RELEASE = 'release';

    /**
     * @var string
     */
    public const string AFTER_RELEASE = 'after-release';

    /**
     * @return string[]
     */
    public static function getAllStages(): array
    {
        return [
            self::RELEASE_CANDIDATE,
            self::RELEASE,
            self::AFTER_RELEASE,
        ];
    }
}
