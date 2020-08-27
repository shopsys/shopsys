<?php

declare(strict_types=1);

namespace App\Component\Router\CategorySeoMix;

use App\Model\CategorySeo\Exception\UnableToFindReadyCategorySeoMixException;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CategorySeoMixUrlGenerator
{
    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private $readyCategorySeoMixFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter
     */
    private $router;

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     * @param \Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter $router
     */
    public function __construct(
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade,
        CurrentDomainRouter $router
    ) {
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
        $this->router = $router;
    }

    /**
     * @param int $categoryId
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function generateUrlWithFallbackToProductList(
        int $categoryId,
        array $parameters,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        try {
            return $this->tryGenerateCategorySeoMixUrl($categoryId, $parameters, $referenceType);
        } catch (UnableToFindReadyCategorySeoMixException $exception) {
            $parameters['id'] = $categoryId;
            return $this->router->generate('front_product_list', $parameters, $referenceType);
        }
    }

    /**
     * @param int $categoryId
     * @param array $parameters
     * @param int $referenceType
     * @return string
     */
    public function tryGenerateCategorySeoMixUrl(
        int $categoryId,
        array $parameters,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $readyCategorySeoMix = $this->readyCategorySeoMixFacade->getCategorySeoMixByRawQueryData($categoryId, $parameters);
        return $this->generateUrlByCategorySeoMixAndQueryParams($readyCategorySeoMix, $parameters, $referenceType);
    }

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix $readyCategorySeoMix
     * @param array $queryParameters
     * @param int $referenceType
     * @return string
     */
    public function generateUrlByCategorySeoMixAndQueryParams(
        ReadyCategorySeoMix $readyCategorySeoMix,
        array $queryParameters,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        // The $readyCategorySeoMix must have an exact match in parameters and flags.
        unset(
            $queryParameters[ReadyCategorySeoMixFacade::FILTER_FORM_KEY]['parameters'],
            $queryParameters[ReadyCategorySeoMixFacade::FILTER_FORM_KEY]['flags']
        );
        $queryParameters['id'] = $readyCategorySeoMix->getId();

        return $this->router->generate('front_category_seo', $queryParameters, $referenceType);
    }
}
