<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Config\Action;

enum ActionType: string
{
    case ENTITY = 'entity';
    case GLOBAL = 'global';
    case DATAGRID = 'datagrid';
}
