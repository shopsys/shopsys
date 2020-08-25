<?php

declare(strict_types=1);

namespace App\Model\Blog\Article\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogArticleNotFoundException extends NotFoundHttpException implements BlogArticleException
{
}
