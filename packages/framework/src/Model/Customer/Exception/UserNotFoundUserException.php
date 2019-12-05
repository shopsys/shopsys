<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserNotFoundUserException extends NotFoundHttpException implements CustomerUserException
{
}
