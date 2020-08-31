<?php

declare(strict_types=1);

namespace App\Model\Gtm;

use App\Component\Domain\Domain;
use App\Model\Category\Category;
use App\Model\Gtm\Data\DataLayerPage;
use App\Model\Gtm\Data\DataLayerUser;
use App\Model\Order\Order;
use App\Model\Order\Preview\SplitOrderPreview;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class GtmFacade
{
    /**
     * @var \App\Model\Gtm\GtmContainer
     */
    private $gtmContainer;

    /**
     * @var \App\Model\Gtm\DataLayer
     */
    private $dataLayer;

    /**
     * @var \App\Model\Gtm\DataLayerMapper
     */
    private $dataLayerMapper;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    private $currentCustomer;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @param \App\Model\Gtm\GtmContainer $gtmContainer
     * @param \App\Model\Gtm\DataLayerMapper $dataLayerMapper
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomer
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        GtmContainer $gtmContainer,
        DataLayerMapper $dataLayerMapper,
        CurrentCustomerUser $currentCustomer,
        Domain $domain,
        CurrencyFacade $currencyFacade
    ) {
        $this->gtmContainer = $gtmContainer;
        $this->dataLayerMapper = $dataLayerMapper;
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;

        $this->dataLayer = $this->gtmContainer->getDataLayer();
        $this->currentCustomer = $currentCustomer;
    }

    /**
     * @param string $routeName
     */
    public function onAllFrontPages(string $routeName): void
    {
        if (!$this->gtmContainer->isEnabled()) {
            return;
        }

        $dataLayerPage = new DataLayerPage();
        $this->dataLayer->set('page', $dataLayerPage);
        $this->dataLayerMapper->mapRouteNameToDataLayerPage($routeName, $dataLayerPage);

        $dataLayerUser = new DataLayerUser();
        $this->dataLayer->set('user', $dataLayerUser);

        $currentCustomer = $this->currentCustomer->findCurrentCustomerUser();
        $this->dataLayerMapper->mapCurrentCustomerToDataLayerUser($currentCustomer, $dataLayerUser);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \App\Model\Product\Listed\ListedProductView[] $listedProductViews
     * @param int $nextIndex
     */
    public function onProductListByCategoryPage(Category $category, array $listedProductViews, int $nextIndex): void
    {
        if (!$this->gtmContainer->isEnabled()) {
            return;
        }

        $dataLayerPage = $this->getDataLayerPage();
        $this->dataLayerMapper->mapCategoryToDataLayerPage($category, $dataLayerPage, $this->dataLayer->getLocale());

        $gtmEventData = [
            'ecommerce' => [
                'currencyCode' => $this->getCurrentDomainDefaultCurrencyCode(),
                'impressions' => $this->dataLayerMapper->createDataLayerProductsFromListedProductViews(
                    $listedProductViews,
                    $nextIndex,
                    null,
                    'Category - standard'
                ),
            ],
        ];

        $this->dataLayer->addEvent($gtmEventData);
    }

    /**
     * @param \App\Model\Product\Listed\ListedProductView[] $listedProductViews
     * @param int $nextIndex
     */
    public function onProductListBySearchPage(array $listedProductViews, int $nextIndex): void
    {
        if (!$this->gtmContainer->isEnabled()) {
            return;
        }

        $gtmEventData = [
            'ecommerce' => [
                'currencyCode' => $this->getCurrentDomainDefaultCurrencyCode(),
                'impressions' => $this->dataLayerMapper->createDataLayerProductsFromListedProductViews($listedProductViews, $nextIndex),
            ],
        ];

        $this->dataLayer->addEvent($gtmEventData);
    }

    /**
     * @param \App\Model\Product\Product $product
     */
    public function onProductDetailPage(Product $product): void
    {
        if (!$this->gtmContainer->isEnabled()) {
            return;
        }

        $dataLayerPage = $this->getDataLayerPage();
        $this->dataLayerMapper->mapProductToDataLayerPage($product, $dataLayerPage, $this->dataLayer->getLocale());

        $gtmEventData = [
            'ecommerce' => [
                'currencyCode' => $this->getCurrentDomainDefaultCurrencyCode(),
                'detail' => [
                    'products' => $this->dataLayerMapper->createDataLayerProductsFromProducts([$product], $this->dataLayer->getLocale()),
                ],
            ],
        ];

        $this->dataLayer->addEvent($gtmEventData);
    }

    /**
     * @param \App\Model\Order\Preview\SplitOrderPreview $splitOrderPreview
     * @param int $orderStep
     */
    public function onOrderPages(SplitOrderPreview $splitOrderPreview, int $orderStep): void
    {
        if (!$this->gtmContainer->isEnabled()) {
            return;
        }

        $dataLayerPage = $this->getDataLayerPage();

        switch ($orderStep) {
            case 2:
                $this->dataLayerMapper->setTypeToDataLayerPage($dataLayerPage, DataLayerPage::TYPE_ORDER_STEP1);
                break;
            case 3:
                $this->dataLayerMapper->setTypeToDataLayerPage($dataLayerPage, DataLayerPage::TYPE_ORDER_STEP2);
                break;
        }

        $gtmEventData = [
            'ecommerce' => [
                'currencyCode' => $this->getCurrentDomainDefaultCurrencyCode(),
                'checkout' => [
                    'actionField' => ['step' => $orderStep - 1],
                    'products' => $this->dataLayerMapper->createDataLayerProductsFromSplitOrderPreview(
                        $splitOrderPreview,
                        $this->dataLayer->getLocale()
                    ),
                ],
            ],
        ];

        $this->dataLayer->addEvent($gtmEventData);
    }

    /**
     * @param \App\Model\Order\Order $order
     */
    public function onOrderSentPage(Order $order): void
    {
        if (!$this->gtmContainer->isEnabled()) {
            return;
        }

        $locale = $this->dataLayer->getLocale();

        $gtmPurchaseEventData = [
            'ecommerce' => [
                'currencyCode' => $order->getCurrency()->getCode(),
                'purchase' => $this->dataLayerMapper->createDataLayerPurchaseFromOrder($order, $locale),
            ],
        ];

        $this->dataLayer->addEvent($gtmPurchaseEventData);
    }

    /**
     * @param \App\Model\Order\Order $order
     */
    public function onOrderNotPaidPage(Order $order): void
    {
        if (!$this->gtmContainer->isEnabled()) {
            return;
        }

        $dataLayerPage = $this->getDataLayerPage();
        $this->dataLayerMapper->setTypeToDataLayerPage($dataLayerPage, DataLayerPage::TYPE_PURCHASE_FAIL);
    }

    /**
     * @param int $code
     */
    public function onErrorPage(int $code): void
    {
        if (!$this->gtmContainer->isEnabled()) {
            return;
        }

        if ($code === 404) {
            $dataLayerPage = $this->getDataLayerPage();
            $this->dataLayerMapper->setTypeToDataLayerPage($dataLayerPage, DataLayerPage::TYPE_ERROR_404);
        }
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $quantity
     */
    public function onAddProductToCart(Product $product, int $quantity): void
    {
        if (!$this->gtmContainer->isEnabled()) {
            return;
        }

        $gtmEventData = [
            'ecommerce' => [
                'currencyCode' => $this->getCurrentDomainDefaultCurrencyCode(),
                'add' => [
                    'products' => $this->dataLayerMapper->createdDataLayerProductsFromAddedOrRemovedProduct(
                        $product,
                        $this->dataLayer->getLocale(),
                        $quantity
                    ),
                ],
            ],
        ];

        $this->dataLayer->push(DataLayer::EVENT_NAME_PRODUCT_ADD_TO_CART, $gtmEventData);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int|null $quantity
     */
    public function onRemoveProductFromCart(Product $product, ?int $quantity = null): void
    {
        if (!$this->gtmContainer->isEnabled()) {
            return;
        }

        $gtmEventData = [
            'ecommerce' => [
                'currencyCode' => $this->getCurrentDomainDefaultCurrencyCode(),
                'remove' => [
                    'products' => $this->dataLayerMapper->createdDataLayerProductsFromAddedOrRemovedProduct(
                        $product,
                        $this->dataLayer->getLocale(),
                        $quantity
                    ),
                ],
            ],
        ];

        $this->dataLayer->push(DataLayer::EVENT_NAME_PRODUCT_REMOVE_FROM_CART, $gtmEventData);
    }

    /**
     * @return string
     */
    private function getCurrentDomainDefaultCurrencyCode(): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        return $currency->getCode();
    }

    /**
     * @return \App\Model\Gtm\Data\DataLayerPage
     */
    private function getDataLayerPage(): DataLayerPage
    {
        $dataLayerPage = $this->dataLayer->get('page');

        if ($dataLayerPage === null) {
            $dataLayerPage = new DataLayerPage();
        }

        return $dataLayerPage;
    }
}
