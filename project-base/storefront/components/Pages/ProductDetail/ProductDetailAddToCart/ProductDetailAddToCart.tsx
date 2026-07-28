import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { CartItemQuantityControls } from 'components/Blocks/Product/CartItemQuantityControls';
import { ProductInquiryButton } from 'components/Blocks/Product/ProductInquiryButton';
import { showWatchdogButton } from 'components/Blocks/Product/Watchdog/WatchDogButton';
import { Button } from 'components/Forms/Button/Button';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { TIDs } from 'cypress/tids';
import { TypeProductDetailFragment } from 'graphql/requests/products/fragments/ProductDetailFragment.generated';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useRef } from 'react';
import { useAddToCartAriaLabel } from 'utils/accessibility/useAddToCartAriaLabel';
import { useAddToCartHandler } from 'utils/cart/useAddToCartHandler';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';

export type ProductDetailAddToCartProps = {
    product: TypeProductDetailFragment;
};

export const ProductDetailAddToCart: FC<ProductDetailAddToCartProps> = ({ product }) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const { t } = useTranslation();
    const { canCreateOrder } = useAuthorization();
    const { cart, isCartFetchingOrUnavailable } = useCurrentCart();
    const cartItem = cart?.items.find(
        (item) => item.type === TypeCartItemTypeEnum.Product && item.product.uuid === product.uuid,
    );

    const { onAddToCartHandler, isAddingToCart } = useAddToCartHandler({
        spinboxRef,
        productUuid: product.uuid,
        gtmMessageOrigin: GtmMessageOriginType.product_detail_page,
        gtmProductListName: GtmProductListNameType.product_detail,
        isWithSpinbox: false,
    });

    const { ariaLabel, onFocusHandler } = useAddToCartAriaLabel({
        spinboxRef,
        productName: product.name,
        priceWithVat: product.price.priceWithVat,
        unitName: product.unit.name,
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
        return <ProductInquiryButton buttonSize="xlarge" productName={product.fullName} productUuid={product.uuid} />;
    }

    if (!canCreateOrder) {
        return null;
    }

    if (isCartFetchingOrUnavailable) {
        return <Skeleton className="h-14 w-full" />;
    }

    if (cartItem) {
        return (
            <CartItemQuantityControls
                cartItem={cartItem}
                gtmMessageOrigin={GtmMessageOriginType.product_detail_page}
                gtmProductListName={GtmProductListNameType.product_detail}
                size="xlarge"
            />
        );
    }

    const isWatchdogButtonVisible = showWatchdogButton(product);

    return (
        <div className="relative">
            {isAddingToCart && (
                <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded-sm bg-background-more py-2 opacity-50" />
            )}

            <Button
                aria-haspopup="dialog"
                aria-label={ariaLabel}
                className="w-full whitespace-nowrap"
                disabled={isAddingToCart}
                hasDisabledLook={isAddingToCart}
                size="xlarge"
                tid={TIDs.pages_productdetail_addtocart_button}
                title={t('Add to cart')}
                variant={isWatchdogButtonVisible ? 'inverted' : 'primary'}
                onClick={onAddToCartHandler}
                onFocus={onFocusHandler}
            >
                <CartIcon className="size-6" />
                {t('Add to cart')}
            </Button>
        </div>
    );
};
