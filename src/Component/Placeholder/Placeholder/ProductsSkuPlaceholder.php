<?php

declare(strict_types = 1);

namespace App\Component\Placeholder\Placeholder;

use App\Model\Product\Listed\ListedProductViewFactory;
use App\Model\Product\ProductOnCurrentDomainFacade;
use Shopsys\ReadModelBundle\Image\ImageViewFacade;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade;
use Twig\Environment;

final class ProductsSkuPlaceholder extends AbstractPlaceholder
{
    public const NAME = 'productsSku';
    public const READABLE_PATTERN = '{products=123,456,789}';
    public const PATTERN = '/(\{products=(?<catnums>[a-zA-Z0-9]+(,[a-zA-Z0-9]+)*)\})/sU';

    /**
     * @var \Twig\Environment
     */
    private $templating;

    /**
     * @var \Shopsys\ReadModelBundle\Image\ImageViewFacade
     */
    private $imageViewFacade;

    /**
     * @var \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade
     */
    private $productActionViewFacade;

    /**
     * @var \App\Model\Product\Listed\ListedProductViewFactory
     */
    private $listedProductViewFactory;

    /**
     * @var \App\Model\Product\ProductOnCurrentDomainFacade
     */
    private $productOnCurrentDomainFacade;

    /**
     * @param \App\Model\Product\ProductOnCurrentDomainFacade $productOnCurrentDomainFacade
     * @param \Twig\Environment $templating
     * @param \App\Model\Product\Listed\ListedProductViewFactory $listedProductViewFactory
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacade $imageViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade $productActionViewFacade
     */
    public function __construct(
        ProductOnCurrentDomainFacade $productOnCurrentDomainFacade,
        Environment $templating,
        ListedProductViewFactory $listedProductViewFactory,
        ImageViewFacade $imageViewFacade,
        ProductActionViewFacade $productActionViewFacade
    ) {
        $this->templating = $templating;
        $this->imageViewFacade = $imageViewFacade;
        $this->productActionViewFacade = $productActionViewFacade;
        $this->listedProductViewFactory = $listedProductViewFactory;
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getPattern(): string
    {
        return self::PATTERN;
    }

    /**
     * @param array $matches
     * @param string|null $locale
     * @return string
     */
    protected function replace(array $matches, ?string $locale): string
    {
        $productCatnums = explode(',', $matches['catnums']);

        $products = $this->productOnCurrentDomainFacade->getVisibleProductsByCatnums($productCatnums);
        if (count($products) === 0) {
            return '';
        }

        //this have to be here, because framework bug
        $productClassName = 'Shopsys\FrameworkBundle\Model\Product\Product';
        $imageViews = $this->imageViewFacade->getForEntityIds($productClassName, $this->getIdsForProducts($products));
        $productActionViews = $this->productActionViewFacade->getForProducts($products);

        $listedProductViews = [];

        foreach ($products as $product) {
            $productId = $product->getId();
            $listedProductViews[$productId] = $this->listedProductViewFactory->createFromProduct($product, $imageViews[$productId], $productActionViews[$productId]);
        }

        return $this->templating->render('Front/Content/Product/productsSkuPlaceholder.html.twig', [
            'products' => $listedProductViews,
        ]);
    }

    /**
     * @param \App\Model\Product\Product[] $products
     * @return int[]
     */
    private function getIdsForProducts(array $products): array
    {
        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product->getId();
        }

        return $productIds;
    }
}
