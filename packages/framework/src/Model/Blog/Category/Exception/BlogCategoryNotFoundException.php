<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogCategoryNotFoundException extends NotFoundHttpException
{
}
