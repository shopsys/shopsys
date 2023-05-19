<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Category\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RootCategoryNotFoundException extends NotFoundHttpException implements CategoryException
{
}
