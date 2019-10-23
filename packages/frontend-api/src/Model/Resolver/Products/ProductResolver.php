<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Products;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Error\UserError;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;

class ProductResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    protected $productFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ProductFacade $productFacade,
        Domain $domain
    ) {
        $this->productFacade = $productFacade;
        $this->domain = $domain;
    }

    /**
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function resolver(string $uuid): Product
    {
        if (Uuid::isValid($uuid) === false) {
            throw new UserError('Provided argument is not valid UUID.');
        }

        try {
            $product = $this->productFacade->getByUuid($uuid);
        } catch (\Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException $productNotFoundException) {
            throw new UserError($productNotFoundException->getMessage());
        }

        if ($product->isVariant() === true) {
            throw new UserError('This product is variant and there is no support for variants yet.');
        }

        return $product;
    }

    /**
     * @return string[]
     */
    public static function getAliases(): array
    {
        return [
            'resolver' => 'product',
        ];
    }
}
