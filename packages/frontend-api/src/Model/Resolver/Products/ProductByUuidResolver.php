<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception\FriendlyUrlNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider;
use Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade;

class ProductByUuidResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider
     */
    protected ProductElasticsearchProvider $productElasticsearchProvider;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @var \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade
     */
    protected FriendlyUrlFacade $friendlyUrlFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductElasticsearchProvider $productElasticsearchProvider
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     */
    public function __construct(
        ProductElasticsearchProvider $productElasticsearchProvider,
        Domain $domain,
        FriendlyUrlFacade $friendlyUrlFacade
    ) {
        $this->productElasticsearchProvider = $productElasticsearchProvider;
        $this->domain = $domain;
        $this->friendlyUrlFacade = $friendlyUrlFacade;
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlSlug
     * @return array
     */
    public function resolver(?string $uuid = null, ?string $urlSlug = null): array
    {
        if ($uuid !== null) {
            return $this->getVisibleProductArrayByUuid($uuid);
        }

        if ($urlSlug !== null) {
            return $this->getVisibleProductArrayOnDomainBySlug($urlSlug);
        }

        throw new UserError('You need to provide argument \'uuid\' or \'urlSlug\'.');
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'productByUuid',
        ];
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
            throw new UserError($productNotFoundException->getMessage());
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
                $urlSlug
            );

            return $this->productElasticsearchProvider->getVisibleProductArrayById($friendlyUrl->getEntityId());
        } catch (FriendlyUrlNotFoundException | ProductNotFoundException $productNotFoundException) {
            throw new UserError('Product with URL slug `' . $urlSlug . '` does not exist.');
        }
    }
}
