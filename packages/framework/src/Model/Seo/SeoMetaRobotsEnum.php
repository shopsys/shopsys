<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class SeoMetaRobotsEnum extends AbstractEnum
{
    public const string NOINDEX = 'noindex';
    public const string NOFOLLOW = 'nofollow';
    public const string NOINDEX_NOFOLLOW = 'noindex, nofollow';

    /**
     * @return array<string, string>
     */
    public function getAllChoices(): array
    {
        $allCases = $this->getAllCases();

        return array_combine($allCases, $allCases);
    }
}
