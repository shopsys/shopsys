<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use ArrayObject;
use BadMethodCallException;
use GraphQL\Type\Definition\ResolveInfo;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;
use Overblog\GraphQLBundle\Resolver\FieldResolver;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\MethodNotFoundException;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper;

class ProductResolverMap extends ResolverMap
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     * @deprecated Used only in deprecated method, will be removed in the next major release
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade
     * @deprecated Used only in deprecated method, will be removed in the next major release
     */
    protected $productCollectionFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade
     * @deprecated Unused, will be removed in the next major release
     */
    protected $flagFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     * @deprecated Unused, will be removed in the next major release
     */
    protected $categoryFacade;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper|null
     */
    protected ?ProductEntityFieldMapper $productEntityFieldMapper;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper|null
     */
    protected ?ProductArrayFieldMapper $productArrayFieldMapper;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper|null $productEntityFieldMapper
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper|null $productArrayFieldMapper
     */
    public function __construct(
        Domain $domain,
        ProductCollectionFacade $productCollectionFacade,
        FlagFacade $flagFacade,
        CategoryFacade $categoryFacade,
        ?ProductEntityFieldMapper $productEntityFieldMapper = null,
        ?ProductArrayFieldMapper $productArrayFieldMapper = null
    ) {
        $this->domain = $domain;
        $this->productCollectionFacade = $productCollectionFacade;
        $this->flagFacade = $flagFacade;
        $this->categoryFacade = $categoryFacade;
        $this->productEntityFieldMapper = $productEntityFieldMapper;
        $this->productArrayFieldMapper = $productArrayFieldMapper;
    }

    /**
     * @required
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper $productArrayFieldMapper
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductArrayFieldMapper(ProductArrayFieldMapper $productArrayFieldMapper): void
    {
        if (
            $this->productArrayFieldMapper !== null
            && $this->productArrayFieldMapper !== $productArrayFieldMapper
        ) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->productArrayFieldMapper !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->productArrayFieldMapper = $productArrayFieldMapper;
    }

    /**
     * @required
     * @param \Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductEntityFieldMapper $productEntityFieldMapper
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductEntityFieldMapper(ProductEntityFieldMapper $productEntityFieldMapper): void
    {
        if (
            $this->productEntityFieldMapper !== null
            && $this->productEntityFieldMapper !== $productEntityFieldMapper
        ) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->productEntityFieldMapper !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->productEntityFieldMapper = $productEntityFieldMapper;
    }

    /**
     * @return array
     */
    protected function map(): array
    {
        return [
            'Product' => [
                self::RESOLVE_TYPE => function ($data) {
                    $isMainVariant = $data instanceof Product ? $data->isMainVariant() : $data['is_main_variant'];
                    $isVariant = $data instanceof Product ? $data->isVariant() : $data['main_variant_id'] !== null;

                    if ($isMainVariant) {
                        return 'MainVariant';
                    }

                    if ($isVariant) {
                        return 'Variant';
                    }

                    return 'RegularProduct';
                },
            ],
            'RegularProduct' => $this->mapProduct(),
            'Variant' => $this->mapProduct(),
            'MainVariant' => $this->mapProduct(),
        ];
    }

    /**
     * @return callable[]
     */
    protected function mapProduct(): array
    {
        return [
            self::RESOLVE_FIELD => function ($value, ArgumentInterface $args, ArrayObject $context, ResolveInfo $info) {
                $mapper = $value instanceof Product ? $this->productEntityFieldMapper : $this->productArrayFieldMapper;

                try {
                    return $this->getObjectMethodForField($mapper, $info->fieldName)($value);
                } catch (MethodNotFoundException $exception) {
                    return FieldResolver::valueFromObjectOrArray($value, $info->fieldName);
                }
            },
        ];
    }

    /**
     * @param object $mapper
     * @param string $fieldName
     * @return callable
     */
    protected function getObjectMethodForField(object $mapper, string $fieldName): callable
    {
        $prefixes = ['get', 'is', ''];

        foreach ($prefixes as $prefix) {
            $methodCandidate = lcfirst($prefix . ucfirst($fieldName));
            if (method_exists($mapper, $methodCandidate)) {
                return [$mapper, $methodCandidate];
            }
        }

        throw new MethodNotFoundException($fieldName, $mapper);
    }

    /**
     * @param int $productId
     * @return string
     * @deprecated Use appropriate class DataMapper\Product*FieldMapper instead
     */
    protected function getProductLink(int $productId): string
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use appropriate field mapper instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        $absoluteUrlsIndexedByProductId = $this->productCollectionFacade->getAbsoluteUrlsIndexedByProductId(
            [$productId],
            $this->domain->getCurrentDomainConfig()
        );

        return $absoluteUrlsIndexedByProductId[$productId];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     * @deprecated Use appropriate class DataMapper\Product*FieldMapper instead
     */
    protected function getFlagsForData($data): array
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use appropriate field mapper instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        if ($data instanceof Product) {
            return $data->getFlags();
        }

        return $this->productArrayFieldMapper->getFlags($data);
    }

    /**
     * @param array $data
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     * @deprecated Use appropriate class DataMapper\Product*FieldMapper instead
     */
    protected function getCategoriesForData($data): array
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use appropriate field mapper instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return $this->productArrayFieldMapper->getCategories($data);
    }
}
