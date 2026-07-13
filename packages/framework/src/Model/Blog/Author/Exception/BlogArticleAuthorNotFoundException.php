<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Author\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BlogArticleAuthorNotFoundException extends NotFoundHttpException
{
}
