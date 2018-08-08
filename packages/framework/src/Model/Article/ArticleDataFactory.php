<?php

namespace Shopsys\FrameworkBundle\Model\Article;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;

class ArticleDataFactory implements ArticleDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    protected $friendlyUrlFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    protected $adminDomainTabsFacade;

    public function __construct(
        FriendlyUrlFacade $friendlyUrlFacade,
        Domain $domain,
        AdminDomainTabsFacade $adminDomainTabsFacade
    ) {
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->domain = $domain;
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
    }

    public function createFromArticle(Article $article): ArticleData
    {
        $articleData = new ArticleData();
        $this->fillFromArticle($articleData, $article);

        return $articleData;
    }

    public function create(): ArticleData
    {
        $articleData = new ArticleData();
        $this->fillNew($articleData);

        return $articleData;
    }

    protected function fillFromArticle(ArticleData $articleData, Article $article): void
    {
        $articleData->name = $article->getName();
        $articleData->text = $article->getText();
        $articleData->seoTitle = $article->getSeoTitle();
        $articleData->seoMetaDescription = $article->getSeoMetaDescription();
        $articleData->domainId = $article->getDomainId();
        $articleData->placement = $article->getPlacement();
        $articleData->hidden = $article->isHidden();
        $articleData->seoH1 = $article->getSeoH1();

        foreach ($this->domain->getAll() as $domainConfig) {
            $articleData->urls->mainFriendlyUrlsByDomainId[$domainConfig->getId()] =
                $this->friendlyUrlFacade->findMainFriendlyUrl(
                    $domainConfig->getId(),
                    'front_article_detail',
                    $article->getId()
                );
        }
    }

    protected function fillNew(ArticleData $articleData): void
    {
        $articleData->domainId = $this->adminDomainTabsFacade->getSelectedDomainId();
    }
}
