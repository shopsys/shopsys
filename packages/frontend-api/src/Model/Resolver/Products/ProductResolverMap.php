<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use BadMethodCallException;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade;
use Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory;
use Shopsys\FrontendApiBundle\Model\Product\ProductAccessoryFacade;

class ProductResolverMap extends ResolverMap
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade
     */
    protected $productCollectionFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade
     */
    protected $flagFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Category\CategoryFacade
     */
    protected $categoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade|null
     */
    protected $brandFacade;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Product\ProductAccessoryFacade|null
     */
    protected $productAccessoryFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser|null
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade|null
     */
    protected $productFacade;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory|null
     */
    protected $parameterWithValuesFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Product\Collection\ProductCollectionFacade $productCollectionFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Flag\FlagFacade $flagFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade|null $brandFacade
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductAccessoryFacade|null $productAccessoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser|null $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade|null $productFacade
     * @param \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory|null $parameterWithValuesFactory
     */
    public function __construct(
        Domain $domain,
        ProductCollectionFacade $productCollectionFacade,
        FlagFacade $flagFacade,
        CategoryFacade $categoryFacade,
        ?BrandFacade $brandFacade = null,
        ?ProductAccessoryFacade $productAccessoryFacade = null,
        ?CurrentCustomerUser $currentCustomerUser = null,
        ?ProductFacade $productFacade = null,
        ?ParameterWithValuesFactory $parameterWithValuesFactory = null
    ) {
        $this->domain = $domain;
        $this->productCollectionFacade = $productCollectionFacade;
        $this->flagFacade = $flagFacade;
        $this->categoryFacade = $categoryFacade;
        $this->brandFacade = $brandFacade;
        $this->productAccessoryFacade = $productAccessoryFacade;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->productFacade = $productFacade;
        $this->parameterWithValuesFactory = $parameterWithValuesFactory;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setBrandFacade(BrandFacade $brandFacade): void
    {
        if ($this->brandFacade !== null && $this->brandFacade !== $brandFacade) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->brandFacade !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->brandFacade = $brandFacade;
    }

    /**
     * @required
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductAccessoryFacade $productAccessoryFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductAccessoryFacade(ProductAccessoryFacade $productAccessoryFacade): void
    {
        if ($this->productAccessoryFacade !== null && $this->productAccessoryFacade !== $productAccessoryFacade) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->productAccessoryFacade !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->productAccessoryFacade = $productAccessoryFacade;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setCurrentCustomerUser(CurrentCustomerUser $currentCustomerUser): void
    {
        if ($this->currentCustomerUser !== null && $this->currentCustomerUser !== $currentCustomerUser) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->currentCustomerUser !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setProductFacade(ProductFacade $productFacade): void
    {
        if ($this->productFacade !== null && $this->productFacade !== $productFacade) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
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
     * @param \Shopsys\FrontendApiBundle\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setParameterWithValuesFactory(ParameterWithValuesFactory $parameterWithValuesFactory): void
    {
        if (
            $this->parameterWithValuesFactory !== null
            && $this->parameterWithValuesFactory !== $parameterWithValuesFactory
        ) {
            throw new BadMethodCallException(
                sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__)
            );
        }
        if ($this->parameterWithValuesFactory !== null) {
            return;
        }

        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );
        $this->parameterWithValuesFactory = $parameterWithValuesFactory;
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
     * @return array
     */
    protected function mapProduct(): array
    {
        return [
            'shortDescription' => function ($data) {
                return $data instanceof Product ? $data->getShortDescription(
                    $this->domain->getId()
                ) : $data['short_description'];
            },
            'link' => function ($data) {
                $productId = $data instanceof Product ? $data->getId() : $data['id'];
                return $this->getProductLink($productId);
            },
            'categories' => function ($data) {
                return $data instanceof Product ? $data->getCategoriesIndexedByDomainId()[$this->domain->getId()] : $this->getCategoriesForData(
                    $data
                );
            },
            'flags' => function ($data) {
                return $this->getFlagsForData($data);
            },
            'availability' => function ($data) {
                return $data instanceof Product ? $data->getCalculatedAvailability() : ['name' => $data['availability']];
            },
            'unit' => function ($data) {
                return $data instanceof Product ? $data->getUnit() : ['name' => $data['unit']];
            },
            'stockQuantity' => function ($data) {
                return $data instanceof Product ? $data->getStockQuantity() : $data['stock_quantity'];
            },
            'isUsingStock' => function ($data) {
                return $data instanceof Product ? $data->isUsingStock() : $data['is_using_stock'];
            },
            'brand' => function ($data) {
                if ($data instanceof Product) {
                    return $data->getBrand();
                }

                if ((int)$data['brand'] > 0) {
                    return $this->brandFacade->getById((int)$data['brand']);
                }

                return null;
            },
            'isSellingDenied' => function ($data) {
                return $data instanceof Product ? $data->getCalculatedSellingDenied() : $data['calculated_selling_denied'];
            },
            'accessories' => function ($data) {
                $product = $data instanceof Product ? $data : $this->productFacade->getById($data['id']);
                return $this->productAccessoryFacade->getAllAccessories(
                    $product,
                    $this->domain->getId(),
                    $this->currentCustomerUser->getPricingGroup()
                );
            },
            'description' => function ($data) {
                return $data instanceof Product ? $data->getDescription($this->domain->getId()) : $data['description'];
            },
            'parameters' => function ($data) {
                $product = $data instanceof Product ? $data : $this->productFacade->getById($data['id']);

                return $this->parameterWithValuesFactory->createMultipleForProduct($product);
            },
            'seoH1' => function ($data) {
                return $data instanceof Product ? $data->getSeoH1($this->domain->getId()) : $data['seo_h1'];
            },
            'seoTitle' => function ($data) {
                return $data instanceof Product ? $data->getSeoTitle($this->domain->getId()) : $data['seo_title'];
            },
            'seoMetaDescription' => function ($data) {
                if ($data instanceof Product) {
                    return $data->getSeoMetaDescription($this->domain->getId());
                }
                return $data['seo_meta_description'];
            },
            'orderingPriority' => function ($data) {
                if ($data instanceof Product) {
                    return $data->getOrderingPriority();
                }
                return $data['ordering_priority'];
            }
        ];
    }

    /**
     * @param int $productId
     * @return string
     */
    protected function getProductLink(int $productId): string
    {
        $absoluteUrlsIndexedByProductId = $this->productCollectionFacade->getAbsoluteUrlsIndexedByProductId(
            [$productId],
            $this->domain->getCurrentDomainConfig()
        );

        return $absoluteUrlsIndexedByProductId[$productId];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product|array $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag[]
     */
    protected function getFlagsForData($data): array
    {
        if ($data instanceof Product) {
            return $data->getFlags();
        }
        $flags = [];
        foreach ($data['flags'] as $flagId) {
            $flags[] = $this->flagFacade->getById($flagId);
        }
        return $flags;
    }

    /**
     * @param array $data
     * @return \Shopsys\FrameworkBundle\Model\Category\Category[]
     */
    protected function getCategoriesForData($data): array
    {
        $categoryIds = $data['categories'];

        $categories = [];
        foreach ($categoryIds as $categoryId) {
            $categories[] = $this->categoryFacade->getById($categoryId);
        }
        return $categories;
    }
}
