<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class FakeHttpException extends HttpException implements ErrorException
{
}
