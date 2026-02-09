<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category\Exception;

use Exception;

class BlogCategoryDomainNotFoundException extends Exception
{
    public function __construct(int $blogCategoryId, int $domainId, ?Exception $previous = null)
    {
        $message = sprintf('BlogCategoryDomain for blog category with ID "%d" and domain ID "%d" not found.', $blogCategoryId, $domainId);

        parent::__construct($message, 0, $previous);
    }
}
