import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import type { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import type { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import type { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { onGtmProductClickEventHandler } from 'gtm/handlers/onGtmProductClickEventHandler';
import { forwardRef } from 'react';
import type { FunctionComponentProps } from 'types/globals';
import type { ProductListViewModeType } from 'types/product';
import { useCurrentCart } from 'utils/cart/useCurrentCart';
import { ProductListItemGridView } from './ProductListItemGridView';
import { ProductListItemListView } from './ProductListItemListView';

export type ProductVisibleItemsConfigType = {
    addToCart?: boolean;
    productListButtons?: boolean;
    storeAvailability?: boolean;
    price?: boolean;
    flags?: boolean;
    discount?: boolean;
    priceFromWord?: boolean;
};

export type { ProductListViewModeType } from 'types/product';

export type ProductItemProps = {
    product: TypeListedProductFragment;
    listIndex: number;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    isProductInComparison: boolean;
    isProductInWishlist: boolean;
    toggleProductInComparison: () => void;
    toggleProductInWishlist: () => void;
    visibleItemsConfig?: ProductVisibleItemsConfigType;
    size?: 'extraSmall' | 'small' | 'medium' | 'large' | 'extraLarge';
    onClick?: (product: TypeListedProductFragment, index: number) => void;
    textSize?: 'xs' | 'sm';
    textSizePrice?: 'base' | 'lg';
    allowKeyboardFocus?: boolean;
    highlightBadgeText?: string;
    productListViewMode?: ProductListViewModeType;
} & FunctionComponentProps;

export const ProductListItem = forwardRef<HTMLLIElement, ProductItemProps>(
    (
        {
            product,
            listIndex,
            gtmProductListName,
            gtmMessageOrigin,
            isProductInComparison,
            isProductInWishlist,
            toggleProductInComparison,
            toggleProductInWishlist,
            className,
            visibleItemsConfig = PREDEFINED_VISIBLE_ITEMS_CONFIGS.largeItem,
            size = 'large',
            textSize = 'sm',
            textSizePrice = 'lg',
            onClick,
            allowKeyboardFocus = true,
            highlightBadgeText,
            productListViewMode = 'grid',
        },
        ref,
    ) => {
        const { url } = useDomainConfig();
        const { canCreateOrder, canSeePrices } = useAuthorization();
        const { cart, isCartFetchingOrUnavailable } = useCurrentCart();
        const currentCart = { cart, isCartFetchingOrUnavailable };
        const isProductActionDependentOnCart =
            visibleItemsConfig.addToCart &&
            canCreateOrder &&
            !product.isSellingDenied &&
            !product.isCurrentlyOutOfStock &&
            (product.isMainVariant || !product.isInquiryType) &&
            !product.isMainVariant;
        const shouldShowProductActionSkeleton = !!isProductActionDependentOnCart && isCartFetchingOrUnavailable;

        const handleProductClick = () => {
            onGtmProductClickEventHandler(product, gtmProductListName, listIndex, url, !canSeePrices);
            onClick?.(product, listIndex);
        };

        if (productListViewMode === 'list') {
            return (
                <ProductListItemListView
                    allowKeyboardFocus={allowKeyboardFocus}
                    className={className}
                    currentCart={currentCart}
                    forwardedRef={ref}
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    highlightBadgeText={highlightBadgeText}
                    isProductInComparison={isProductInComparison}
                    isProductInWishlist={isProductInWishlist}
                    listIndex={listIndex}
                    product={product}
                    shouldShowProductActionSkeleton={shouldShowProductActionSkeleton}
                    toggleProductInComparison={toggleProductInComparison}
                    toggleProductInWishlist={toggleProductInWishlist}
                    visibleItemsConfig={visibleItemsConfig}
                    onProductClick={handleProductClick}
                />
            );
        }

        return (
            <ProductListItemGridView
                allowKeyboardFocus={allowKeyboardFocus}
                className={className}
                currentCart={currentCart}
                forwardedRef={ref}
                gtmMessageOrigin={gtmMessageOrigin}
                gtmProductListName={gtmProductListName}
                highlightBadgeText={highlightBadgeText}
                isProductInComparison={isProductInComparison}
                isProductInWishlist={isProductInWishlist}
                listIndex={listIndex}
                product={product}
                shouldShowProductActionSkeleton={shouldShowProductActionSkeleton}
                size={size}
                textSize={textSize}
                textSizePrice={textSizePrice}
                toggleProductInComparison={toggleProductInComparison}
                toggleProductInWishlist={toggleProductInWishlist}
                visibleItemsConfig={visibleItemsConfig}
                onProductClick={handleProductClick}
            />
        );
    },
);

ProductListItem.displayName = 'ProductItem';

export const PREDEFINED_VISIBLE_ITEMS_CONFIGS = {
    largeItem: {
        productListButtons: true,
        addToCart: true,
        flags: true,
        discount: false,
        price: true,
        storeAvailability: true,
        priceFromWord: true,
    } as ProductVisibleItemsConfigType,
    mediumItem: {
        flags: true,
        discount: false,
        price: true,
        storeAvailability: true,
        priceFromWord: true,
    } as ProductVisibleItemsConfigType,
} as const;
