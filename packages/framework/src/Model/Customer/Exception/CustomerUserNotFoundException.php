<?php

namespace Shopsys\FrameworkBundle\Model\Customer\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerUserNotFoundException extends NotFoundHttpException implements CustomerUserException
{
}
