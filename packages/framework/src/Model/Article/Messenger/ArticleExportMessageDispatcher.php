<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Article\Messenger;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;

class ArticleExportMessageDispatcher extends AbstractMessageDispatcher
{
    /**
     * @param int $articleId
     * @param int $domainId
     */
    public function dispatchArticleExportMessage(int $articleId, int $domainId): void
    {
        $this->messageBus->dispatch(new ArticleExportMessage($articleId, $domainId));
    }

    /**
     * @param int[] $articleIds
     * @param int $domainId
     */
    public function dispatchArticleExportMessages(array $articleIds, int $domainId): void
    {
        foreach ($articleIds as $articleId) {
            $this->messageBus->dispatch(new ArticleExportMessage($articleId, $domainId));
        }
    }
}
