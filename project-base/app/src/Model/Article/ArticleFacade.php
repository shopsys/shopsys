<?php

declare(strict_types=1);

namespace App\Model\Article;

use App\Model\Article\Elasticsearch\ArticleExportScheduler;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Article\ArticleData;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade as BaseArticleFacade;
use Shopsys\FrameworkBundle\Model\Article\ArticleFactoryInterface;
use Shopsys\FrameworkBundle\Model\Article\ArticleRepository;

/**
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
 * @property \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
 * @method \App\Model\Article\Article|null findById(int $articleId)
 * @method \App\Model\Article\Article getById(int $articleId)
 * @method \App\Model\Article\Article getVisibleById(int $articleId)
 * @method \App\Model\Article\Article[] getVisibleArticlesForPlacementOnCurrentDomain(string $placement)
 * @method \App\Model\Article\Article[] getAllByDomainId(int $domainId)
 */
class ArticleFacade extends BaseArticleFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleRepository $articleRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFactoryInterface $articleFactory
     * @param \App\Model\Article\Elasticsearch\ArticleExportScheduler $articleExportScheduler
     */
    public function __construct(
        EntityManagerInterface $em,
        ArticleRepository $articleRepository,
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade,
        ArticleFactoryInterface $articleFactory,
        private ArticleExportScheduler $articleExportScheduler,
    ) {
        parent::__construct(
            $em,
            $articleRepository,
            $domain,
            $friendlyUrlFacade,
            $articleFactory,
        );
    }

    /**
     * @param \App\Model\Article\ArticleData $articleData
     * @return \App\Model\Article\Article
     */
    public function create(ArticleData $articleData): Article
    {
        /** @var \App\Model\Article\Article $article */
        $article = parent::create($articleData);

        $this->articleExportScheduler->scheduleRowIdForImmediateExport($article->getId());

        return $article;
    }

    /**
     * @param int $articleId
     * @param \App\Model\Article\ArticleData $articleData
     * @return \App\Model\Article\Article
     */
    public function edit($articleId, ArticleData $articleData): Article
    {
        /** @var \App\Model\Article\Article $article */
        $article = parent::edit($articleId, $articleData);

        $this->articleExportScheduler->scheduleRowIdForImmediateExport($article->getId());

        return $article;
    }

    /**
     * @param int[][] $rowIdsByGridId
     */
    public function saveOrdering(array $rowIdsByGridId): void
    {
        foreach ($rowIdsByGridId as $gridId => $rowIds) {
            foreach ($rowIds as $position => $rowId) {
                /** @var \App\Model\Article\Article $article */
                $article = $this->articleRepository->findById($rowId);

                if ($article->getPosition() !== $position || $article->getPlacement() !== $gridId) {
                    $this->articleExportScheduler->scheduleRowIdForImmediateExport($article->getId());
                }

                $article->setPosition($position);
                $article->setPlacement($gridId);
            }
        }
        $this->em->flush();
    }

    /**
     * @param int $articleId
     */
    public function delete($articleId)
    {
        parent::delete($articleId);

        $this->articleExportScheduler->scheduleRowIdForImmediateExport((int)$articleId);
    }
}
