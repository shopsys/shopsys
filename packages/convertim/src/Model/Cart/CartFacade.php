<?php

declare(strict_types=1);

namespace Shopsys\ConvertimBundle\Model\Cart;

use Convertim\Cart\ConvertimCartData;
use Convertim\Cart\ConvertimCartItem;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\AdministratorIsNotLoggedException;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade;

class CartFacade
{
    public const string KEY_EXTRA_CREATED_AS_ADMIN = 'createdAsAdministrator';
    public const string KEY_EXTRA_CREATED_AS_ADMIN_NAME = 'createdAsAdministratorName';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Security\AdministratorFrontSecurityFacade $administratorFrontSecurityFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductCachedAttributesFacade $productCachedAttributesFacade
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly AdministratorFrontSecurityFacade $administratorFrontSecurityFacade,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly ProductCachedAttributesFacade $productCachedAttributesFacade,
        protected readonly ImageFacade $imageFacade,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart|null $cart
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Convertim\Cart\ConvertimCartData
     */
    public function getCartData(?Cart $cart, ?CustomerUser $customerUser): ConvertimCartData
    {
        if ($cart === null) {
            return new ConvertimCartData([], []);
        }

        $cartDataItems = $this->mapCartItemData($cart, $customerUser);

        return new ConvertimCartData(
            $cartDataItems,
            $this->convertimPromoCodeFacade->getValidEnteredConvertimPromoCodes(),
            $this->getExtraData(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return \Convertim\Cart\ConvertimCartItem[]
     */
    protected function mapCartItemData(Cart $cart, ?CustomerUser $customerUser): array
    {
        $domainConfig = $this->domain->getCurrentDomainConfig();
        $domainId = $domainConfig->getId();
        $locale = $domainConfig->getLocale();
        $cartDataItems = [];
        $orderData = $this->orderDataFactory->createFromCart($cart, $this->domain->getCurrentDomainConfig());

        foreach ($cart->getItems() as $cartItem) {
            if (!$cartItem->hasProduct()) {
                continue;
            }

            $product = $cartItem->getProduct();
            $mainCategory = $this->categoryFacade->findProductMainCategoryByDomainId($product, $domainId);
            $productPrice = $this->productCachedAttributesFacade->getProductSellingPrice($product);
            $mainImage = $this->imageFacade->getImageByEntity($product, null);

            if ($productPrice === null) {
                continue;
            }

            $cartDataItems[] = new ConvertimCartItem(
                (string)$product->getId(),
                $product->getName(),
                $cartItem->getQuantity(),
                $productPrice->getPriceWithoutVat()->getAmount(),
                $productPrice->getPriceWithVat()->getAmount(),
                $product->getVatForDomain($domainId)->getPercent(),
                $this->imageFacade->getImageUrl($domainConfig, $mainImage),
                [
                    'brand' => $product->getBrand()?->getName(),
                    'category' => $mainCategory?->getName(),
                    'labels' => array_map(static function ($flag) use ($locale) {
                        return $flag->getName($locale);
                    }, $product->getFlags($domainId)),
                ],
                [$orderData->promoCode => $orderData->totalPricesByItemType[OrderItemTypeEnum::TYPE_DISCOUNT]->inverse()],
                [],
                [],
                [
                    'pricingGroupId' => $customerUser?->getPricingGroup()?->getId(),
                ],
                $this->productAvailabilityFacade->getProductAvailabilityStatusByDomainId($cartItem, $domainId),
            );
        }

        return $cartDataItems;
    }

    /**
     * @return array
     */
    protected function getExtraData(): array
    {
        if ($this->administratorFrontSecurityFacade->isAdministratorLogged()) {
            try {
                $currentAdmin = $this->administratorFrontSecurityFacade->getCurrentAdministrator();
                $createdAsAdministrator = $currentAdmin;
                $createdAsAdministratorName = $currentAdmin->getRealName();
            } catch (AdministratorIsNotLoggedException) {
            }
        }

        return [
            static::KEY_EXTRA_CREATED_AS_ADMIN => $createdAsAdministrator ?? null,
            static::KEY_EXTRA_CREATED_AS_ADMIN_NAME => $createdAsAdministratorName ?? null,
        ];
    }
}
