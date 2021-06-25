<?php

declare(strict_types=1);

namespace App\Model\Navigation\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NavigationItemNotFoundException extends NotFoundHttpException
{
}
