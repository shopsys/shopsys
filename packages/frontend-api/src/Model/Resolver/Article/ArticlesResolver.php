<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Article;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Article\ArticleFacade;

class ArticlesResolver implements ResolverInterface, AliasedInterface
{
    protected const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Article\ArticleFacade
     */
    protected $articleFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder
     */
    protected $connectionBuilder;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Article\ArticleFacade $articleFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(ArticleFacade $articleFacade, Domain $domain)
    {
        $this->articleFacade = $articleFacade;
        $this->domain = $domain;
        $this->connectionBuilder = new ConnectionBuilder();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param string|null $placement
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function resolve(Argument $argument, ?string $placement)
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);
        $domainId = $this->domain->getId();

        $paginator = new Paginator(function ($offset, $limit) use ($domainId, $placement) {
            return $this->getArticlesList($domainId, $limit, $offset, $placement);
        });

        return $paginator->auto($argument, $this->getArticlesCount($domainId, $placement));
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     */
    protected function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false
            && $argument->offsetExists('last') === false
        ) {
            $argument->offsetSet('first', static::DEFAULT_FIRST_LIMIT);
        }
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'articles',
        ];
    }

    /**
     * @param int $domainId
     * @param int $limit
     * @param int $offset
     * @param string|null $placement
     * @return \Shopsys\FrameworkBundle\Model\Article\Article[]
     */
    protected function getArticlesList(
        int $domainId,
        int $limit,
        int $offset,
        ?string $placement
    ): array {
        if ($placement === null) {
            return $this->articleFacade->getVisibleArticlesListByDomainId(
                $domainId,
                $limit,
                $offset
            );
        }

        return $this->articleFacade->getVisibleArticlesListByDomainIdAndPlacement(
            $domainId,
            $placement,
            $limit,
            $offset
        );
    }

    /**
     * @param int $domainId
     * @param string|null $placement
     * @return int
     */
    protected function getArticlesCount(int $domainId, ?string $placement): int
    {
        if ($placement === null) {
            return $this->articleFacade->getAllVisibleArticlesCountByDomainId($domainId);
        }
        return $this->articleFacade->getAllVisibleArticlesCountByDomainIdAndPlacement($domainId, $placement);
    }
}
