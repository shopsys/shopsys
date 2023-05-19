<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerUserNotFoundException extends NotFoundHttpException implements CustomerUserException
{
}
