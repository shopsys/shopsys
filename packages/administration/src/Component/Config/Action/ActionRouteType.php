<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action;

enum ActionRouteType: string
{
    case CRUD = 'crud';
    case URL = 'url';
    case ROUTE = 'route';
    case NONE = 'none';
}
