<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Messenger;

class ArticleExportMessage
{
    /**
     * @param int $articleId
     * @param int $domainId
     */
    public function __construct(
        public readonly int $articleId,
        public readonly int $domainId,
    ) {
    }
}
