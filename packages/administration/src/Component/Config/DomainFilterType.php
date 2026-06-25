<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config;

enum DomainFilterType
{
    case NONE;
    case SCALAR;
    case COLLECTION;
}
