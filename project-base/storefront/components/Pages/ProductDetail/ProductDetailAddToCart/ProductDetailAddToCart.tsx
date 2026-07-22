import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { AdditionalServices } from 'components/Blocks/Product/AdditionalServices/AdditionalServices';
import { CartItemQuantityControls } from 'components/Blocks/Product/CartItemQuantityControls';
import { ProductInquiryButton } from 'components/Blocks/Product/ProductInquiryButton';
import { showWatchdogButton } from 'components/Blocks/Product/Watchdog/WatchDogButton';
import { SkeletonModuleProductDetailAddToCart } from 'components/Blocks/Skeleton/SkeletonModuleProductDetailAddToCart';
import { Button, getButtonIconClassName } from 'components/Forms/Button/Button';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useRef } from 'react';
import { useAddToCartHandler } from 'utils/cart/useAddToCartHandler';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useProductAdditionalServices } from 'utils/cart/useProductAdditionalServices';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export type ProductDetailAddToCartProps = {
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    buttonTid?: string;
    product: TypeProductDetailFragment;
    shouldDisplayAdditionalServices?: boolean;
    spinboxId?: string;
};

export const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = ({
    buttonSize = 'xlarge',
    buttonTid = TIDs.pages_productdetail_addtocart_button,
    product,
    shouldDisplayAdditionalServices = true,
    spinboxId,
}) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const { t } = useTranslation();
    const { canCreateOrder } = useAuthorization();
    const { isCartFetchingOrUnavailable } = useCurrentCart();

    const {
        cartItem,
        isAddToCartFlowPending,
        selectedServiceUuids,
        updateIsAddToCartFlowPending,
        onToggleService,
        persistPendingServicesAfterAddToCart,
        isSettingAdditionalServices,
    } = useProductAdditionalServices({
        productUuid: product.uuid,
        gtmProductListName: GtmProductListNameType.product_detail,
    });

    const { onAddToCartHandler, isAddingToCart } = useAddToCartHandler({
        spinboxRef,
        productUuid: product.uuid,
        gtmMessageOrigin: GtmMessageOriginType.product_detail_page,
        gtmProductListName: GtmProductListNameType.product_detail,
        isWithSpinbox: false,
        onAddToCartFlowStateChange: updateIsAddToCartFlowPending,
        onProductAddedToCart: (addedCartItem) => persistPendingServicesAfterAddToCart(addedCartItem.uuid),
    });
    const isAddToCartPending = isAddingToCart || isAddToCartFlowPending;

    const addToCartAriaLabel = t('Add to cart {{ productName }}, quantity {{ quantity }} {{ unit }}', {
        ns: 'accessibility',
        productName: product.fullName,
        quantity: 1,
        unit: product.unit.name,
    });

    if (product.isSellingDenied) {
        return <p className="text-text-error">{t('This item can no longer be purchased')}</p>;
    }

    if (product.isCurrentlyOutOfStock) {
        return (
            <p className="text-text-error">
                {t('This item is currently out of stock and cannot be purchased at the moment.')}
            </p>
        );
    }

    if (product.isInquiryType) {
        return (
            <ProductInquiryButton buttonSize={buttonSize} productName={product.fullName} productUuid={product.uuid} />
        );
    }

    if (!canCreateOrder) {
        return null;
    }

    if (isCartFetchingOrUnavailable) {
        return <SkeletonModuleProductDetailAddToCart size={buttonSize} />;
    }

    const additionalServicesElement = shouldDisplayAdditionalServices ? (
        <AdditionalServices
            additionalServices={product.additionalServices}
            isDisabled={isAddToCartPending || isSettingAdditionalServices}
            quantity={cartItem?.quantity}
            selectedServiceUuids={selectedServiceUuids}
            tidDiscriminator={product.catalogNumber}
            unitName={product.unit.name}
            showSelectedServiceTotalPrice
            onToggleService={onToggleService}
        />
    ) : null;

    if (cartItem && !isAddToCartPending) {
        return (
            <div className="flex flex-col gap-4">
                <CartItemQuantityControls
                    cartItem={cartItem}
                    className="w-full sm:max-w-60"
                    gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                    gtmProductListName={GtmProductListNameType.product_detail}
                    size={buttonSize}
                    spinboxId={spinboxId}
                />

                {additionalServicesElement}
            </div>
        );
    }

    const isWatchdogButtonVisible = showWatchdogButton(product);

    return (
        <div className="flex flex-col gap-4">
            <div className="relative w-full sm:max-w-60">
                {isAddToCartPending && (
                    <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded-sm bg-background-more py-2 opacity-50" />
                )}

                <Button
                    aria-haspopup="dialog"
                    aria-label={addToCartAriaLabel}
                    className="w-full whitespace-nowrap"
                    disabled={isAddToCartPending}
                    hasDisabledLook={isAddToCartPending}
                    size={buttonSize}
                    tid={buttonTid}
                    variant={isWatchdogButtonVisible ? 'secondary' : 'primary'}
                    onClick={onAddToCartHandler}
                >
                    <CartIcon className={getButtonIconClassName(buttonSize)} />
                    {t('Add to cart')}
                </Button>
            </div>

            {additionalServicesElement}
        </div>
    );
};
