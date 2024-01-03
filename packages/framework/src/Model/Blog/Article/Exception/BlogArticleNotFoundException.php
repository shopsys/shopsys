<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogArticleNotFoundException extends NotFoundHttpException
{
}
