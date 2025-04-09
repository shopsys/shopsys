<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid;

enum OrderingEnum: string
{
    case ASC = 'asc';
    case DESC = 'desc';
}
