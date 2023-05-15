<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrontendApiBundle\Model\Error\InvalidArgumentUserError;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Products\Exception\ProductNotFoundUserError;

class ProductDetailQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        protected readonly ProductElasticsearchProvider $productElasticsearchProvider,
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
    ) {
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return array
     */
    public function productDetailQuery(?string $uuid = null, ?string $urlSlug = null): array
    {
        if ($uuid !== null) {
            return $this->getVisibleProductArrayByUuid($uuid);
        }

        if ($urlSlug !== null) {
            return $this->getVisibleProductArrayOnDomainBySlug($urlSlug);
        }

        throw new InvalidArgumentUserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @param string $uuid
     * @return array
     */
    protected function getVisibleProductArrayByUuid(string $uuid): array
    {
        try {
            return $this->productElasticsearchProvider->getVisibleProductArrayByUuid($uuid);
        } catch (ProductNotFoundException $productNotFoundException) {
            throw new ProductNotFoundUserError($productNotFoundException->getMessage());
        }
    }

    /**
     * @param string $urlSlug
     * @return array
     */
    protected function getVisibleProductArrayOnDomainBySlug(string $urlSlug): array
    {
        try {
            $friendlyUrl = $this->friendlyUrlFacade->getFriendlyUrlByRouteNameAndSlug(
                $this->domain->getId(),
                'front_product_detail',
                $urlSlug,
            );

            return $this->productElasticsearchProvider->getVisibleProductArrayById($friendlyUrl->getEntityId());
        } catch (FriendlyUrlNotFoundException | ProductNotFoundException $productNotFoundException) {
            throw new ProductNotFoundUserError('Product with URL slug `' . $urlSlug . '` does not exist.');
        }
    }
}
