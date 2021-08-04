<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Article;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Article\Article;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;

class ArticleResolverMap extends ResolverMap
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Component\Router\FriendlyUrl\FriendlyUrlFacade
     */
    private FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(Domain $domain, FriendlyUrlFacade $friendlyUrlFacade)
    {
        $this->friendlyUrlFacade = $friendlyUrlFacade;
        $this->domain = $domain;
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        $map['Article'] = [
            'slug' => function (Article $article) {
                return $this->getSlug($article);
            },
        ];

        return $map;
    }

    /**
     * @param \App\Model\Article\Article $article
     * @return string
     */
    private function getSlug(Article $article): string
    {
        $friendlyUrl = $this->friendlyUrlFacade->getMainFriendlyUrl(
            $this->domain->getId(),
            'front_article_detail',
            $article->getId()
        );

        return $friendlyUrl->getSlug();
    }
}
