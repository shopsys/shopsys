import { CartIcon } from 'components/Basic/Icon/CartIcon';
import { Loader } from 'components/Basic/Loader/Loader';
import { Skeleton } from 'components/Basic/Skeleton/Skeleton';
import { CartItemQuantityControls } from 'components/Blocks/Product/CartItemQuantityControls';
import { Button, getButtonIconClassName } from 'components/Forms/Button/Button';
import { TIDs } from 'cypress/tids';
import { TypeCartItemTypeEnum } from 'graphql/types';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useRef } from 'react';
import { CurrentCartType } from 'types/cart';
import { useAddToCartAriaLabel } from 'utils/accessibility/useAddToCartAriaLabel';
import { OnProductAddedToCart } from 'utils/cart/useAddToCart';
import { useAddToCartHandler } from 'utils/cart/useAddToCartHandler';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';

type AddToCartProps = {
    productUuid: string;
    minQuantity: number;
    maxQuantity: number | null;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    buttonVariant?: 'primary' | 'secondary';
    tabIndex?: number;
    ariaProductName: string;
    ariaPrice: string;
    ariaUnit: string;
    onProductAddedToCart?: OnProductAddedToCart;
};

type AddToCartContentProps = AddToCartProps & Pick<CurrentCartType, 'cart' | 'isCartFetchingOrUnavailable'>;

export const AddToCartContent: FC<AddToCartContentProps> = ({
    cart,
    productUuid,
    isCartFetchingOrUnavailable,
    gtmMessageOrigin,
    gtmProductListName,
    listIndex,
    minQuantity,
    className,
    buttonSize = 'medium',
    buttonVariant = 'primary',
    tabIndex = 0,
    ariaProductName,
    ariaPrice,
    ariaUnit,
    onProductAddedToCart,
}) => {
    const spinboxRef = useRef<HTMLInputElement | null>(null);
    const { t } = useTranslation();
    const cartItem = cart?.items.find(
        (item) => item.type === TypeCartItemTypeEnum.Product && item.product.uuid === productUuid,
    );

    const { onAddToCartHandler, isAddingToCart } = useAddToCartHandler({
        spinboxRef,
        productUuid,
        gtmMessageOrigin,
        gtmProductListName,
        isWithSpinbox: false,
        listIndex,
        onProductAddedToCart,
    });

    const { ariaLabel, onFocusHandler } = useAddToCartAriaLabel({
        spinboxRef,
        productName: ariaProductName,
        priceWithVat: ariaPrice,
        unitName: ariaUnit,
    });
    const fallbackAriaLabel = t('Add to cart {{ productName }}, quantity {{ quantity }} {{ unit }}', {
        ns: 'accessibility',
        productName: ariaProductName,
        quantity: minQuantity,
        unit: ariaUnit,
    });

    if (isCartFetchingOrUnavailable) {
        return <Skeleton className={twMergeCustom('h-9 w-1/2', className)} />;
    }

    if (cartItem) {
        return (
            <CartItemQuantityControls
                cartItem={cartItem}
                className={className}
                gtmMessageOrigin={gtmMessageOrigin}
                gtmProductListName={gtmProductListName}
                listIndex={listIndex}
                size={buttonSize}
            />
        );
    }

    return (
        <div className={twMergeCustom('relative', className)}>
            {isAddingToCart && (
                <Loader className="absolute inset-0 z-overlay flex h-full w-full items-center justify-center rounded-sm bg-background-more py-2 opacity-50" />
            )}

            <Button
                aria-haspopup="dialog"
                aria-label={ariaLabel ?? fallbackAriaLabel}
                disabled={isAddingToCart}
                hasDisabledLook={isAddingToCart}
                name="add-to-cart"
                className="w-full"
                size={buttonSize}
                tabIndex={tabIndex}
                tid={TIDs.blocks_product_addtocart}
                variant={buttonVariant}
                onClick={onAddToCartHandler}
                onFocus={onFocusHandler}
            >
                <CartIcon className={getButtonIconClassName(buttonSize)} />
                {t('Add to cart')}
            </Button>
        </div>
    );
};

export const AddToCart: FC<AddToCartProps> = (props) => {
    const { cart, isCartFetchingOrUnavailable } = useCurrentCart();

    return <AddToCartContent {...props} cart={cart} isCartFetchingOrUnavailable={isCartFetchingOrUnavailable} />;
};
