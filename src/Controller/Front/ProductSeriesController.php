<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Product\Listed\ListedProductViewFactory;
use App\Model\Product\Series\Category\ProductSeriesCategoryFacade;
use App\Model\Product\Series\ProductSeriesFacadeInterface;
use App\Model\Product\Series\ProductSeriesProductFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\ReadModelBundle\Image\ImageViewFacade;
use Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade;
use Symfony\Component\HttpFoundation\Response;

class ProductSeriesController extends FrontBaseController
{
    /**
     * @var \App\Model\Product\Series\ProductSeriesFacadeInterface
     */
    private $productSeriesFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \App\Model\Product\Series\Category\ProductSeriesCategoryFacade
     */
    private $productSeriesCategoryFacade;

    /**
     * @var \App\Model\Product\Series\ProductSeriesProductFacade
     */
    private $productSeriesProductFacade;

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
     * @param \App\Model\Product\Series\ProductSeriesFacadeInterface $productSeriesFacade
     * @param \App\Model\Product\Series\Category\ProductSeriesCategoryFacade $productSeriesCategoryFacade
     * @param \App\Model\Product\Series\ProductSeriesProductFacade $productSeriesProductFacade
     * @param \Shopsys\ReadModelBundle\Image\ImageViewFacade $imageViewFacade
     * @param \Shopsys\ReadModelBundle\Product\Action\ProductActionViewFacade $productActionViewFacade
     * @param \App\Model\Product\Listed\ListedProductViewFactory $listedProductViewFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ProductSeriesFacadeInterface $productSeriesFacade,
        ProductSeriesCategoryFacade $productSeriesCategoryFacade,
        ProductSeriesProductFacade $productSeriesProductFacade,
        ImageViewFacade $imageViewFacade,
        ProductActionViewFacade $productActionViewFacade,
        ListedProductViewFactory $listedProductViewFactory,
        Domain $domain
    ) {
        $this->productSeriesFacade = $productSeriesFacade;
        $this->domain = $domain;
        $this->productSeriesCategoryFacade = $productSeriesCategoryFacade;
        $this->productSeriesProductFacade = $productSeriesProductFacade;
        $this->imageViewFacade = $imageViewFacade;
        $this->productActionViewFacade = $productActionViewFacade;
        $this->listedProductViewFactory = $listedProductViewFactory;
    }

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailAction(int $id): Response
    {
        $productSeries = $this->productSeriesFacade->getVisibleProductSeriesByIdAndDomainId($id, $this->domain->getId());

        $products = $this->productSeriesProductFacade->findAvailableProductsByProductSeries($productSeries);

        //this has to be here, because framework bug https://github.com/shopsys/shopsys/issues/1693
        $productClassName = 'Shopsys\FrameworkBundle\Model\Product\Product';
        $imageViews = $this->imageViewFacade->getForEntityIds($productClassName, $this->getIdsForProducts($products));
        $productActionViews = $this->productActionViewFacade->getForProducts($products);

        $listedProductViews = [];

        foreach ($products as $product) {
            $productId = $product->getId();
            $listedProductViews[$productId] = $this->listedProductViewFactory->createFromProduct(
                $product,
                $imageViews[$productId],
                $productActionViews[$productId]
            );
        }

        return $this->render('Front/Content/ProductSeries/detail.html.twig', [
            'productSeries' => $productSeries,
            'products' => $listedProductViews,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        $productSeries = $this->productSeriesFacade->getAllVisibleProductSeriesByDomainId($this->domain->getId());
        $productSeriesCategories = $this->productSeriesCategoryFacade->getSortedProductSeriesCategoriesFilteredByProductSeries($productSeries);

        return $this->render('Front/Content/ProductSeries/list.html.twig', [
            'productSeriesList' => $productSeries,
            'productSeriesCategories' => $productSeriesCategories,
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
