<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Products\DataMapper;

use Shopsys\FrontendApiBundle\Model\Resolver\Products\DataMapper\ProductArrayFieldMapper as BaseProductArrayFieldMapper;

/**
 * @property \App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method __construct(\App\FrontendApi\Model\Parameter\ParameterWithValuesFactory $parameterWithValuesFactory, \Overblog\DataLoader\DataLoaderInterface $productsSellableByIdsBatchLoader, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Overblog\DataLoader\DataLoaderInterface $productsVisibleByIdsBatchLoader, \Overblog\DataLoader\DataLoaderInterface $productsVisibleCountByIdsBatchLoader, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Seo\HreflangLinksFacade $hreflangLinksFacade, \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade, \Shopsys\FrontendApiBundle\Model\ProductReview\ProductReviewApiFacade $productReviewApiFacade, \Overblog\DataLoader\DataLoaderInterface $additionalServicesByIdsBatchLoader, \Overblog\DataLoader\DataLoaderInterface $categoriesBatchLoader, \Shopsys\FrontendApiBundle\Model\Seo\SeoAttributesQueryDtoFactory $seoAttributesQueryDtoFactory, \Overblog\DataLoader\DataLoaderInterface $flagsBatchLoader, \Overblog\DataLoader\DataLoaderInterface $brandsBatchLoader)
 */
class ProductArrayFieldMapper extends BaseProductArrayFieldMapper
{
}
