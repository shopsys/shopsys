<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArticleNotFoundException extends NotFoundHttpException implements ArticleException
{
}
