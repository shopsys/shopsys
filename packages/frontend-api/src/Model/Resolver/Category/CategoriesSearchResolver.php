<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Category;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrontendApiBundle\Model\Category\CategoryFacade;

class CategoriesSearchResolver implements ResolverInterface, AliasedInterface
{
    protected const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Category\CategoryFacade
     */
    protected CategoryFacade $categoryFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(Domain $domain, CategoryFacade $categoryFacade)
    {
        $this->domain = $domain;
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function resolveSearch(Argument $argument)
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $searchText = $argument['search'] ?? '';

        $paginator = new Paginator(function ($offset, $limit) use ($searchText) {
            return $this->categoryFacade->getVisibleCategoriesBySearchText(
                $searchText,
                $this->domain->getLocale(),
                $this->domain->getId(),
                $offset,
                $limit
            );
        });

        return $paginator->auto(
            $argument,
            $this->categoryFacade->getVisibleCategoriesBySearchTextCount(
                $searchText,
                $this->domain->getLocale(),
                $this->domain->getId()
            )
        );
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
            'resolveSearch' => 'categoriesSearch',
        ];
    }
}
