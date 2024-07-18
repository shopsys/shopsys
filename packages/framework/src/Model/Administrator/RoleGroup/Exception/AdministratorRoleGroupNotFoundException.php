<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\Exception;

use Shopsys\FrameworkBundle\Model\Administrator\Exception\AdministratorException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdministratorRoleGroupNotFoundException extends NotFoundHttpException implements AdministratorException
{
}
