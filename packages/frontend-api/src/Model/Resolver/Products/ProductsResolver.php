<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use BadMethodCallException;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface;
use Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade;

class ProductsResolver implements ResolverInterface, AliasedInterface
{
    protected const DEFAULT_FIRST_LIMIT = 10;
    /**
     * @deprecated This will be removed in next major release
     */
    protected const EDGE_COUNT = 2;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface
     * @deprecated This property will be removed in next major version
     */
    protected $productOnCurrentDomainFacade;

    /**
     * @var \Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder
     */
    protected $connectionBuilder;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Product\ProductFacade|null
     */
    protected $productFacade;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade|null
     */
    protected ?ProductFilterFacade $productFilterFacade;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory|null
     */
    protected ?ProductConnectionFactory $productConnectionFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade|null $productFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade|null $productFilterFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory|null $productConnectionFactory
     */
    public function __construct(
        ProductOnCurrentDomainFacadeInterface $productOnCurrentDomainFacade,
        ?ProductFacade $productFacade = null,
        ?ProductFilterFacade $productFilterFacade = null,
        ?ProductConnectionFactory $productConnectionFactory = null
    ) {
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
        $this->connectionBuilder = new ConnectionBuilder();
        $this->productFacade = $productFacade;
        $this->productFilterFacade = $productFilterFacade;
        $this->productConnectionFactory = $productConnectionFactory;
    }

    /**
     * @required
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductFacade $productFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductFacade(ProductFacade $productFacade): void
    {
        if ($this->productFacade !== null && $this->productFacade !== $productFacade) {
            throw new BadMethodCallException(sprintf(
                'Method "%s" has been already called and cannot be called multiple times.',
                __METHOD__
            ));
        }
        if ($this->productFacade !== null) {
            return;
        }
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->productFacade = $productFacade;
    }

    /**
     * @required
     * @param \Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade $productFilterFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductFilterFacade(ProductFilterFacade $productFilterFacade): void
    {
        if ($this->productFilterFacade !== null && $this->productFilterFacade !== $productFilterFacade) {
            throw new BadMethodCallException(sprintf(
                'Method "%s" has been already called and cannot be called multiple times.',
                __METHOD__
            ));
        }

        if ($this->productFilterFacade !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        $this->productFilterFacade = $productFilterFacade;
    }

    /**
     * @required
     * @param \Shopsys\FrontendApiBundle\Model\Product\Connection\ProductConnectionFactory $productConnectionFactory
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductConnectionFactory(ProductConnectionFactory $productConnectionFactory): void
    {
        if (
            $this->productConnectionFactory !== null
            && $this->productConnectionFactory !== $productConnectionFactory
        ) {
            throw new BadMethodCallException(sprintf(
                'Method "%s" has been already called and cannot be called multiple times.',
                __METHOD__
            ));
        }

        if ($this->productConnectionFactory !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        $this->productConnectionFactory = $productConnectionFactory;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function resolve(Argument $argument)
    {
        $search = $argument['search'] ?? '';

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForAll(
            $argument
        );

        return $this->productConnectionFactory->createConnectionForAll(
            function ($offset, $limit) use ($argument, $productFilterData, $search) {
                return $this->productFacade->getFilteredProductsOnCurrentDomain(
                    $limit,
                    $offset,
                    $this->getOrderingModeFromArgument($argument),
                    $productFilterData,
                    $search
                );
            },
            $this->productFacade->getFilteredProductsCountOnCurrentDomain($productFilterData, $search),
            $argument,
            $productFilterData
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function resolveByCategory(Argument $argument, Category $category)
    {
        $search = $argument['search'] ?? '';

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForCategory(
            $argument,
            $category
        );

        return $this->productConnectionFactory->createConnectionForCategory(
            $category,
            function ($offset, $limit) use ($argument, $category, $productFilterData, $search) {
                return $this->productFacade->getFilteredProductsByCategory(
                    $category,
                    $limit,
                    $offset,
                    $this->getOrderingModeFromArgument($argument),
                    $productFilterData,
                    $search
                );
            },
            $this->productFacade->getFilteredProductsByCategoryCount($category, $productFilterData, $search),
            $argument,
            $productFilterData
        );
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|object
     */
    public function resolveByBrand(Argument $argument, Brand $brand)
    {
        $search = $argument['search'] ?? '';

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $productFilterData = $this->productFilterFacade->getValidatedProductFilterDataForBrand(
            $argument,
            $brand
        );

        return $this->productConnectionFactory->createConnectionForBrand(
            $brand,
            function ($offset, $limit) use ($argument, $brand, $productFilterData, $search) {
                return $this->productFacade->getFilteredProductsByBrand(
                    $brand,
                    $limit,
                    $offset,
                    $this->getOrderingModeFromArgument($argument),
                    $productFilterData,
                    $search
                );
            },
            $this->productFacade->getFilteredProductsByBrandCount($brand, $productFilterData, $search),
            $argument,
            $productFilterData
        );
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

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string
     */
    protected function getOrderingModeFromArgument(Argument $argument): string
    {
        $orderingMode = $this->getDefaultOrderingMode($argument);

        if ($argument->offsetExists('orderingMode')) {
            $orderingMode = $argument->offsetGet('orderingMode');
        }

        return $orderingMode;
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return string
     */
    protected function getDefaultOrderingMode(Argument $argument): string
    {
        if (isset($argument['search'])) {
            return ProductListOrderingConfig::ORDER_BY_RELEVANCE;
        }

        return ProductListOrderingConfig::ORDER_BY_PRIORITY;
    }
}
