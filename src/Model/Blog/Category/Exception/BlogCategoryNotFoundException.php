<?php

declare(strict_types=1);

namespace App\Model\Blog\Category\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogCategoryNotFoundException extends NotFoundHttpException implements BlogCategoryException
{
}
