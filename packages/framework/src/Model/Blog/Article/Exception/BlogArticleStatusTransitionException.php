<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article\Exception;

use Exception;

class BlogArticleStatusTransitionException extends Exception
{
    /**
     * @param array<int, string> $blockerMessages
     */
    public function __construct(string $domainName, array $blockerMessages, ?Exception $previous = null)
    {
        $message = t('Unable to change status for domain %name%:', [
            '%name%' => $domainName,
        ]) . ' ' . implode(' ', $blockerMessages);

        parent::__construct($message, 0, $previous);
    }
}
