import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { CartItemQuantityControls } from 'components/Blocks/Product/CartItemQuantityControls';
import { ProductInquiryButton } from 'components/Blocks/Product/ProductInquiryButton';
import { SkeletonModuleProductDetailAddToCart } from 'components/Blocks/Skeleton/SkeletonModuleProductDetailAddToCart';
import { Button } from 'components/Forms/Button/Button';
import { getButtonIconClassName } from 'components/Forms/Button/buttonUtils';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useRef } from 'react';
import { twJoin } from 'tailwind-merge';
import { useAddToCartHandler } from 'utils/cart/useAddToCartHandler';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { useProductAdditionalServices } from 'utils/cart/useProductAdditionalServices';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showWatchdogButton } from 'utils/product/showWatchdogButton';

export type ProductDetailAddToCartProps = {
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    buttonTid?: string;
    isFullWidth?: boolean;
    product: TypeProductDetailFragment;
    spinboxId?: string;
};

export const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = ({
    buttonSize = 'xlarge',
    buttonTid = TIDs.pages_productdetail_addtocart_button,
    isFullWidth = false,
    product,
    spinboxId,
}) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const { t } = useTranslation();
    const { canCreateOrder } = useAuthorization();
    const { isCartFetchingOrUnavailable } = useCurrentCart();

    const { cartItem, isAddToCartFlowPending, updateIsAddToCartFlowPending, persistPendingServicesAfterAddToCart } =
        useProductAdditionalServices({
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
            <ProductInquiryButton
                buttonSize={buttonSize}
                className={isFullWidth ? 'w-full' : 'sm:max-w-60'}
                productName={product.fullName}
                productUuid={product.uuid}
            />
        );
    }

    if (!canCreateOrder) {
        return null;
    }

    if (isCartFetchingOrUnavailable) {
        return <SkeletonModuleProductDetailAddToCart isFullWidth={isFullWidth} size={buttonSize} />;
    }

    if (cartItem && !isAddToCartPending) {
        return (
            <CartItemQuantityControls
                cartItem={cartItem}
                className={twJoin(
                    'w-full',
                    !isFullWidth && 'sm:max-w-60',
                    buttonSize === 'xlarge' && '[&_button]:size-14',
                )}
                gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                gtmProductListName={GtmProductListNameType.product_detail}
                size={buttonSize}
                spinboxId={spinboxId}
            />
        );
    }

    const isWatchdogButtonVisible = showWatchdogButton(product);

    return (
        <div className={twJoin('relative flex w-full', !isFullWidth && 'sm:max-w-60')}>
            {isAddToCartPending && (
                <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded-sm bg-background-more py-2 opacity-50" />
            )}

            <Button
                aria-haspopup="dialog"
                aria-label={addToCartAriaLabel}
                className={twJoin(
                    'h-auto w-full whitespace-normal text-balance',
                    buttonSize !== 'xlarge' && 'min-h-9',
                    buttonSize === 'large' && 'sm:min-h-10',
                    buttonSize === 'xlarge' && 'min-h-14',
                )}
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
    );
};
