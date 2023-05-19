<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Exception;

use Exception;

class DeletingLastAdministratorException extends Exception implements AdministratorException
{
}
