<?php

declare(strict_types=1);

namespace App\Twig;

use App\Model\Product\Listing\ProductListOrderingModeForListFacade;
use Override;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ProductOrderingExtension extends AbstractExtension
{
    public function __construct(
        private readonly ProductListOrderingModeForListFacade $productListOrderingModeForListFacade,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'getOrderingNameByOrderingId',
                $this->getOrderingNameByOrderingId(...),
            ),
        ];
    }

    public function getOrderingNameByOrderingId(?string $orderingId): string
    {
        if ($orderingId === null) {
            return '';
        }

        $supportedOrderingModesNamesIndexedById = $this->productListOrderingModeForListFacade
            ->getProductListOrderingConfig()
            ->getSupportedOrderingModesNamesIndexedById();

        return $supportedOrderingModesNamesIndexedById[$orderingId] ?? t('Unsupported order') . ' ' . $orderingId;
    }
}
