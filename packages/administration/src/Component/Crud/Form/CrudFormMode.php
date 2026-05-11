<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Crud\Form;

enum CrudFormMode: string
{
    case UNCONFIGURED = 'unconfigured';
    case FORM_TYPE = 'formType';
    case BUILDER = 'builder';
}
