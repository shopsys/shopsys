<?php

declare(strict_types=1);

namespace Shopsys\LuigisBoxBundle\Model\Endpoint;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

class LuigisBoxEndpointEnum extends AbstractEnum
{
    public const string AUTOCOMPLETE = 'autocomplete/v2';
    public const string SEARCH = 'search';
}
