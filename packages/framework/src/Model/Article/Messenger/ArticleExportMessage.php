<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Messenger;

class ArticleExportMessage
{
    public function __construct(
        public readonly int $articleId,
        public readonly int $domainId,
    ) {
    }
}
