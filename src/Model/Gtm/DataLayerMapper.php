<?php

declare(strict_types = 1);

namespace App\Model\Gtm;

use App\Component\Domain\Domain;
use App\Model\Category\Category;
use App\Model\Category\CategoryFacade;
use App\Model\Customer\User\CustomerUser as BaseCustomer;
use App\Model\Gtm\Data\DataLayerPage;
use App\Model\Gtm\Data\DataLayerProduct;
use App\Model\Gtm\Data\DataLayerUser;
use App\Model\Order\Item\OrderItem;
use App\Model\Order\Order;
use App\Model\Order\Preview\SplitOrderPreview;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use App\Model\Product\Listed\ListedProductView;
use App\Model\Product\Product;
use App\Model\Product\ProductCachedAttributesFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class DataLayerMapper
{
    private const PRICE_SCALE = 3;

    private const ROUTE_NAMES_TO_PAGE_TYPE = [
        'front_homepage' => DataLayerPage::TYPE_HOME,
        'front_article_detail' => DataLayerPage::TYPE_ARTICLE,
        'front_article_list' => DataLayerPage::TYPE_BLOG,
        'front_blogcategory_detail' => DataLayerPage::TYPE_BLOG,
        'front_blogarticle_detail' => DataLayerPage::TYPE_BLOG_ARTICLE,
        'front_cart' => DataLayerPage::TYPE_CART,
        'front_product_detail' => DataLayerPage::TYPE_PRODUCT,
        'front_order_sent' => DataLayerPage::TYPE_PURCHASE,
        'front_product_search' => DataLayerPage::TYPE_SEARCH,
        'front_product_list' => DataLayerPage::TYPE_CATEGORY,
        'front_error' => DataLayerPage::TYPE_ERROR,
    ];

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var \App\Model\Product\ProductCachedAttributesFacade
     */
    private $productCachedAttributesFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade
     */
    private $administratorFrontSecurityFacade;

    /**
     * @var \App\Model\Product\Availability\ProductAvailabilityFacade
     */
    private $productAvailabilityFacade;

    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface $authorizationChecker
     * @param \App\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        Domain $domain,
        AuthorizationCheckerInterface $authorizationChecker,
        ProductCachedAttributesFacade $productCachedAttributesFacade,
        AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        ProductAvailabilityFacade $productAvailabilityFacade
    ) {
        $this->categoryFacade = $categoryFacade;
        $this->domain = $domain;
        $this->authorizationChecker = $authorizationChecker;
        $this->productCachedAttributesFacade = $productCachedAttributesFacade;
        $this->administratorFrontSecurityFacade = $administratorFrontSecurityFacade;
        $this->productAvailabilityFacade = $productAvailabilityFacade;
    }

    /**
     * @param string $routeName
     * @param \App\Model\Gtm\Data\DataLayerPage $dataLayerPage
     */
    public function mapRouteNameToDataLayerPage(string $routeName, DataLayerPage $dataLayerPage): void
    {
        $dataLayerPage->setType(
            self::ROUTE_NAMES_TO_PAGE_TYPE[$routeName] ?? DataLayerPage::TYPE_OTHER
        );
    }

    /**
     * @param \App\Model\Gtm\Data\DataLayerPage $dataLayerPage
     * @param string $pageType
     */
    public function setTypeToDataLayerPage(DataLayerPage $dataLayerPage, string $pageType): void
    {
        $dataLayerPage->setType($pageType);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $currentCustomer
     * @param \App\Model\Gtm\Data\DataLayerUser $dataLayerUser
     */
    public function mapCurrentCustomerToDataLayerUser(?BaseCustomer $currentCustomer, DataLayerUser $dataLayerUser): void
    {
        if ($currentCustomer !== null) {
            $dataLayerUser->setId((string)$currentCustomer->getId());
            $dataLayerUser->setState(DataLayerUser::STATE_LOGGED_IN);

            if ($this->authorizationChecker->isGranted(Roles::ROLE_ADMIN)
                || $this->authorizationChecker->isGranted(Roles::ROLE_SUPER_ADMIN)
            ) {
                $dataLayerUser->setType(DataLayerUser::TYPE_ADMIN);
            } else {
                $dataLayerUser->setType(DataLayerUser::TYPE_CUSTOMER);
            }
        } else {
            if ($this->administratorFrontSecurityFacade->isAdministratorLogged()) {
                $dataLayerUser->setState(DataLayerUser::STATE_LOGGED_IN);
                $dataLayerUser->setType(DataLayerUser::TYPE_ADMIN);
            } else {
                $dataLayerUser->setState(DataLayerUser::STATE_ANONYMOUS);
                $dataLayerUser->setType(DataLayerUser::TYPE_VISITOR);
            }
        }
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \App\Model\Gtm\Data\DataLayerPage $dataLayerPage
     * @param string $locale
     */
    public function mapCategoryToDataLayerPage(Category $category, DataLayerPage $dataLayerPage, string $locale): void
    {
        $this->mapCategoryToDataLayerPageCategory($category, $dataLayerPage, $locale);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \App\Model\Gtm\Data\DataLayerPage $dataLayerPage
     * @param string $locale
     */
    private function mapCategoryToDataLayerPageCategory(Category $category, DataLayerPage $dataLayerPage, string $locale): void
    {
        $categoriesInPath = $this->categoryFacade->getCategoriesInPath($category);
        $categoriesIdsInPath = [];
        $categoriesNamesInPath = [];
        foreach ($categoriesInPath as $categoryInPath) {
            /** @var \App\Model\Category\Category $categoryInPath */
            $categoriesIdsInPath[] = (string)$categoryInPath->getId();
            $categoriesNamesInPath[] = $categoryInPath->getName($locale);
        }

        $dataLayerPage->setCategoryIds($categoriesIdsInPath);
        $dataLayerPage->setCategory($categoriesNamesInPath);
        $dataLayerPage->setCategoryLevel((string)$category->getLevel());
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Gtm\Data\DataLayerPage $dataLayerPage
     * @param string $locale
     */
    public function mapProductToDataLayerPage(Product $product, DataLayerPage $dataLayerPage, string $locale): void
    {
        /** @var \App\Model\Category\Category $productMainCategory */
        $productMainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $this->domain->getId());

        $this->mapCategoryToDataLayerPageCategory($productMainCategory, $dataLayerPage, $locale);
    }

    /**
     * @param \App\Model\Product\Product[] $products
     * @param string $locale
     * @return \App\Model\Gtm\Data\DataLayerProduct[]
     */
    public function createDataLayerProductsFromProducts(array $products, string $locale): array
    {
        $dataLayerProducts = [];
        foreach ($products as $product) {
            $dataLayerProduct = new DataLayerProduct();
            $this->mapProductToDataLayerProduct($product, $dataLayerProduct, $locale);
            $dataLayerProducts[] = $dataLayerProduct;
        }

        return $dataLayerProducts;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Gtm\Data\DataLayerProduct $dataLayerProduct
     * @param string $locale
     */
    public function mapProductToDataLayerProduct(Product $product, DataLayerProduct $dataLayerProduct, string $locale): void
    {
        $dataLayerProduct->setName((string)$product->getName($locale));
        $dataLayerProduct->setId((string)$product->getId());

        $sellingPrice = $this->productCachedAttributesFacade->getProductSellingPrice($product);

        if ($sellingPrice !== null) {
            $dataLayerProduct->setPrice($sellingPrice->getPriceWithoutVat()->getAmount());
            $dataLayerProduct->setTax($sellingPrice->getVatAmount()->getAmount());
            $dataLayerProduct->setPriceWithTax($sellingPrice->getPriceWithVat()->getAmount());
        }

        /** @var \App\Model\Category\Category $productMainCategory */
        $productMainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $this->domain->getId());
        $dataLayerProduct->setCategory($this->categoryFacade->getCategoriesNamesInPathAsString($productMainCategory, $locale));
        $dataLayerProduct->setAvailability(
            $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId(
                $product,
                $this->domain->getId()
            )
        );

        $dataLayerProduct->setTags(array_map(function ($flag) use ($locale) {
            /** @var \App\Model\Product\Flag\Flag $flag */
            return $flag->getName($locale);
        }, $product->getFlagsForDomain($this->domain->getId())));
    }

    /**
     * @param \App\Model\Product\Listed\ListedProductView[] $listedProductViews
     * @param \App\Model\Category\Category $category
     * @param string $locale
     * @return \App\Model\Gtm\Data\DataLayerProduct[]
     */
    public function createDataLayerProductsFromListedProductViews(array $listedProductViews, Category $category, string $locale): array
    {
        $dataLayerProducts = [];
        $i = 1;
        foreach ($listedProductViews as $listedProductView) {
            $dataLayerProduct = new DataLayerProduct();
            $this->mapListedProductViewToDataLayerProduct($listedProductView, $dataLayerProduct, $category, $i++, $locale);
            $dataLayerProducts[] = $dataLayerProduct;
        }

        return $dataLayerProducts;
    }

    /**
     * @param \App\Model\Product\Listed\ListedProductView $listedProductView
     * @param \App\Model\Gtm\Data\DataLayerProduct $dataLayerProduct
     * @param \App\Model\Category\Category $category
     * @param int $position
     * @param string $locale
     */
    public function mapListedProductViewToDataLayerProduct(
        ListedProductView $listedProductView,
        DataLayerProduct $dataLayerProduct,
        Category $category,
        int $position,
        string $locale
    ): void {
        $dataLayerProduct->setName((string)$listedProductView->getName());
        $dataLayerProduct->setId((string)$listedProductView->getId());

        $sellingPrice = $listedProductView->getSellingPrice();

        if ($sellingPrice !== null) {
            $dataLayerProduct->setPrice($sellingPrice->getPriceWithoutVat()->getAmount());
            $dataLayerProduct->setTax($sellingPrice->getVatAmount()->getAmount());
            $dataLayerProduct->setPriceWithTax($sellingPrice->getPriceWithVat()->getAmount());
        }

        $dataLayerProduct->setCategory($this->categoryFacade->getCategoriesNamesInPathAsString($category, $locale));
        $dataLayerProduct->setAvailability($listedProductView->getAvailability());
        $dataLayerProduct->setPosition($position);
//        $dataLayerProduct->setList($category->get);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @param string $locale
     * @return array
     */
    public function createDataLayerPurchaseFromOrder(Order $order, string $locale): array
    {
        $productItems = $order->getProductItems();
        $productsData = [];
        foreach ($productItems as $productItem) {
            $product = $productItem->getProduct();

            if ($product === null) {
                continue;
            }

            $productsData[] = $this->createDataLayerPurchaseProductFromOrderProductItem($productItem, $locale);
        }

        $revenue = $order->getTotalPriceWithoutVat()
            ->subtract($order->getOrderTransport()->getPriceWithoutVat())
            ->subtract($order->getOrderPayment()->getPriceWithoutVat());
        $revenueWithTax = $order->getTotalPriceWithVat()
            ->subtract($order->getOrderTransport()->getPriceWithVat())
            ->subtract($order->getOrderPayment()->getPriceWithVat());
        $shipping = $order->getOrderTransport()->getPriceWithoutVat();
        $shippingTax = $order->getOrderTransport()->getPriceWithVat()->subtract($order->getOrderTransport()->getPriceWithoutVat());

        $payment = $order->getOrderPayment()->getPriceWithoutVat();
        $paymentTax = $order->getOrderPayment()->getPriceWithVat()->subtract($order->getOrderPayment()->getPriceWithoutVat());

        $tax = $order->getTotalVatAmount()->subtract($shippingTax)->subtract($paymentTax);

        $dataLayerPurchase = [
            'actionField' => [
                'id' => $order->getNumber(),
                'revenue' => $this->getMoneyAsString($revenue),
                'revenueWithTax' => $this->getMoneyAsString($revenueWithTax),
                'tax' => $this->getMoneyAsString($tax),
                'shipping' => $this->getMoneyAsString($shipping->add($payment)),
                'totalPaidByCustomer' => $this->getMoneyAsString($order->getTotalPriceWithVat()),
            ],
            'products' => $productsData,
        ];

        if ($order->getGtmCoupon() !== null) {
            $dataLayerPurchase['actionField']['coupon'] = $order->getGtmCoupon();
        }

        return $dataLayerPurchase;
    }

    /**
     * @param \App\Model\Order\Item\OrderItem $productItem
     * @param string $locale
     * @return array
     */
    private function createDataLayerPurchaseProductFromOrderProductItem(OrderItem $productItem, string $locale): array
    {
        /** @var \App\Model\Product\Product $product */
        $product = $productItem->getProduct();

        $priceWithVat = $productItem->getPriceWithVat();
        $priceWithoutVat = $productItem->getPriceWithoutVat();
        $vat = $priceWithVat->subtract($priceWithoutVat);

        /** @var \App\Model\Category\Category $productMainCategory */
        $productMainCategory = $this->categoryFacade->getProductMainCategoryByDomainId($product, $this->domain->getId());

        $productData = [
            'name' => $product->getName($locale),
            'id' => $product->getId(),
            'price' => $this->getMoneyAsString($priceWithoutVat),
            'tax' => $this->getMoneyAsString($vat),
            'priceWithTax' => $this->getMoneyAsString($priceWithVat),
            'category' => $this->categoryFacade->getCategoriesNamesInPathAsString($productMainCategory, $locale),
            'availability' => $this->productAvailabilityFacade->getProductAvailabilityInformationByDomainId($product, $this->domain->getId()),
            'quantity' => $productItem->getQuantity(),
        ];

        $coupon = $productItem->getRelatedCoupon() ? $productItem->getRelatedCoupon()->getDiscountText() : null;
        if ($coupon !== null) {
            $productData['coupon'] = $coupon;
        }

        $flags = $product->getFlagsForDomain($this->domain->getId());
        foreach ($flags as $flag) {
            $productData['tags'][] = $flag->getName($locale);
        }

        return $productData;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $quantity
     * @param string $locale
     * @return \App\Model\Gtm\Data\DataLayerProduct[]
     */
    public function createdDataLayerProductsFromAddedProduct(Product $product, int $quantity, string $locale): array
    {
        $dataLayerProduct = new DataLayerProduct();
        $this->mapProductWithQuantityToDataLayerProduct($product, $dataLayerProduct, $quantity, $locale);

        return [$dataLayerProduct];
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Gtm\Data\DataLayerProduct $dataLayerProduct
     * @param int $quantity
     * @param string $locale
     */
    private function mapProductWithQuantityToDataLayerProduct(
        Product $product,
        DataLayerProduct $dataLayerProduct,
        int $quantity,
        string $locale
    ): void {
        $this->mapProductToDataLayerProduct($product, $dataLayerProduct, $locale);
        $dataLayerProduct->setQuantity($quantity);
    }

    /**
     * @param \App\Model\Order\Preview\SplitOrderPreview $splitOrderPreview
     * @param string $locale
     * @return \App\Model\Gtm\Data\DataLayerProduct[]
     */
    public function createDataLayerProductsFromSplitOrderPreview(SplitOrderPreview $splitOrderPreview, string $locale): array
    {
        $dataLayerProducts = [];
        foreach ($splitOrderPreview->getOrderPreviews() as $orderPreview) {
            foreach ($orderPreview->getQuantifiedProducts() as $quantifiedProduct) {
                $dataLayerProduct = new DataLayerProduct();
                $this->mapProductWithQuantityToDataLayerProduct(
                    $quantifiedProduct->getProduct(),
                    $dataLayerProduct,
                    $quantifiedProduct->getQuantity(),
                    $locale
                );
                $dataLayerProducts[] = $dataLayerProduct;
            }
        }

        return $dataLayerProducts;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    private function getMoneyAsString(Money $price): string
    {
        return $price->round(self::PRICE_SCALE)->getAmount();
    }
}
