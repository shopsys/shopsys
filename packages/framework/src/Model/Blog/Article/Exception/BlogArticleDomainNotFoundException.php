<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Exception;

use Exception;

class BlogArticleDomainNotFoundException extends Exception
{
    public function __construct(int $blogArticleId, int $domainId, ?Exception $previous = null)
    {
        $message = sprintf('BlogArticleDomain for blog article with ID %d and domain ID %d not found.', $blogArticleId, $domainId);

        parent::__construct($message, 0, $previous);
    }
}
