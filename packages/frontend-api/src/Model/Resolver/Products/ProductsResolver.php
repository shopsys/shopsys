<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Doctrine\ORM\Query;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Doctrine\SortableNullsWalker;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade;

class ProductsResolver implements ResolverInterface, AliasedInterface
{
    protected const DEFAULT_FIRST_LIMIT = 10;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade
     */
    protected $productOnCurrentDomainFacade;

    /**
     * @var \Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder
     */
    protected $connectionBuilder;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacade $productOnCurrentDomainFacade
     */
    public function __construct(
        ProductOnCurrentDomainFacade $productOnCurrentDomainFacade
    ) {
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
        $this->connectionBuilder = new ConnectionBuilder();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function resolve(Argument $argument)
    {
        if ($argument->offsetExists('last')) {
            $limit = (int)$argument->offsetGet('last');
            $cursor = $argument->offsetGet('before');
            $offset = max((int)$this->connectionBuilder->cursorToOffset($cursor) - $limit, 0);
        } else {
            $this->setDefaultFirstOffsetIfNecessary($argument);
            $limit = (int)$argument->offsetGet('first');
            $cursor = $argument->offsetGet('after');
            $offset = (int)$this->connectionBuilder->cursorToOffset($cursor);
        }

        $products = $this->getProductsForAll($offset, $limit);
        $paginator = new Paginator(function () use ($products) {
            return $products;
        });

        return $paginator->auto($argument, count($products));
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function resolveByCategory(Argument $argument, Category $category)
    {
        if ($argument->offsetExists('last')) {
            $limit = (int)$argument->offsetGet('last');
            $cursor = $argument->offsetGet('before');
            $offset = max((int)$this->connectionBuilder->cursorToOffset($cursor) - $limit, 0);
        } else {
            $this->setDefaultFirstOffsetIfNecessary($argument);
            $limit = (int)$argument->offsetGet('first');
            $cursor = $argument->offsetGet('after');
            $offset = (int)$this->connectionBuilder->cursorToOffset($cursor);
        }

        $products = $this->getProductsByCategory($category, $offset, $limit);
        $paginator = new Paginator(function () use ($products) {
            return $products;
        });

        return $paginator->auto($argument, count($products));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $offset
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getProductsByCategory(Category $category, int $offset, int $limit): array
    {
        $queryBuilder = $this->productOnCurrentDomainFacade->getAllListableTranslatedAndOrderedQueryBuilderByCategory(
            ProductListOrderingConfig::ORDER_BY_PRIORITY,
            $category
        );

        $queryBuilder->setFirstResult($offset)
            ->setMaxResults($limit + 2);
        $query = $queryBuilder->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class);

        return $query->execute();
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getProductsForAll(int $offset, int $limit): array
    {
        $queryBuilder = $this->productOnCurrentDomainFacade->getAllListableTranslatedAndOrderedQueryBuilder(
            ProductListOrderingConfig::ORDER_BY_PRIORITY
        );

        $queryBuilder->setFirstResult($offset)
            ->setMaxResults($limit + 2);
        $query = $queryBuilder->getQuery();
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class);

        return $query->execute();
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     */
    protected function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false) {
            $argument->offsetSet('first', static::DEFAULT_FIRST_LIMIT);
        }
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolve' => 'products',
        ];
    }
}
