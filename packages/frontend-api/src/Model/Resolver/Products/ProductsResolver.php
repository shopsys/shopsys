<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;

class ProductsResolver implements ResolverInterface, AliasedInterface
{
    protected const DEFAULT_FIRST_LIMIT = 10;
    /**
     * @deprecated This will be removed in next major release
     */
    protected const EDGE_COUNT = 2;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     */
    protected $productOnCurrentDomainFacade;

    /**
     * @var \Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder
     */
    protected $connectionBuilder;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     */
    public function __construct(
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
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
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $paginator = new Paginator(function ($offset, $limit) {
            return $this->productOnCurrentDomainFacade->getProductsOnCurrentDomain(
                $limit,
                $offset,
                ProductListOrderingConfig::ORDER_BY_PRIORITY
            );
        });

        return $paginator->auto($argument, $this->productOnCurrentDomainFacade->getProductsCountOnCurrentDomain());
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function resolveByCategory(Argument $argument, Category $category)
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $paginator = new Paginator(function ($offset, $limit) use ($category) {
            return $this->productOnCurrentDomainFacade->getProductsByCategory(
                $category,
                $limit,
                $offset,
                ProductListOrderingConfig::ORDER_BY_PRIORITY
            );
        });

        return $paginator->auto($argument, $this->productOnCurrentDomainFacade->getProductsCountOnCurrentDomain());
    }
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     */
    protected function setDefaultFirstOffsetIfNecessary(Argument $argument): void
    {
        if ($argument->offsetExists('first') === false && $argument->offsetExists('last') === false) {
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
