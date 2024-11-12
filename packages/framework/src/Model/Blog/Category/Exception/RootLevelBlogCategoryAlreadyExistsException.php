<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Category\Exception;

use Exception;

class RootLevelBlogCategoryAlreadyExistsException extends Exception
{
    /**
     * @param int $blogCategoryId
     * @param \Exception|null $previous
     */
    public function __construct(int $blogCategoryId, ?Exception $previous = null)
    {
        $message = sprintf('There can be only one blog category with root level. The current root level blog category ID is "%d".', $blogCategoryId);

        parent::__construct($message, 0, $previous);
    }
}
