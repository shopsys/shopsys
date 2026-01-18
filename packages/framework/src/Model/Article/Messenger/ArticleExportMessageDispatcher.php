<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Messenger;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;

class ArticleExportMessageDispatcher extends AbstractMessageDispatcher
{
    public function dispatchArticleExportMessage(int $articleId, int $domainId): void
    {
        $this->messageBus->dispatch(new ArticleExportMessage($articleId, $domainId));
    }

    /**
     * @param array<int, int> $articleIds
     */
    public function dispatchArticleExportMessages(array $articleIds, int $domainId): void
    {
        foreach ($articleIds as $articleId) {
            $this->messageBus->dispatch(new ArticleExportMessage($articleId, $domainId));
        }
    }
}
