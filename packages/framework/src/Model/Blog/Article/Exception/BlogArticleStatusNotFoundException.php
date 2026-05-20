<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Exception;

use Exception;

class BlogArticleStatusNotFoundException extends Exception
{
    public function __construct(string $status, ?Exception $previous = null)
    {
        $message = sprintf('Blog article status "%s" not found.', $status);

        parent::__construct($message, 0, $previous);
    }
}
