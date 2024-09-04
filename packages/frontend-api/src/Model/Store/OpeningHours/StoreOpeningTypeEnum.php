<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Store\OpeningHours;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class StoreOpeningTypeEnum extends AbstractEnum
{
    public const string STATUS_OPEN = 'OPEN';
    public const string STATUS_CLOSED = 'CLOSED';
    public const string STATUS_OPEN_SOON = 'OPEN_SOON';
    public const string STATUS_CLOSED_SOON = 'CLOSED_SOON';
}
