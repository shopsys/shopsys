import { ProductCompareButton } from 'components/Blocks/Product/ButtonsAction/ProductCompareButton';
import { ProductWishlistButton } from 'components/Blocks/Product/ButtonsAction/ProductWishlistButton';
import { ProductAction } from 'components/Blocks/Product/ProductAction';
import { ProductActionSkeleton } from 'components/Blocks/Skeleton/ProductActionSkeleton';
import type { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import type { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import type { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import type { CurrentCartType } from 'types/cart';
import type { ProductItemProps, ProductVisibleItemsConfigType } from './ProductListItem';

type ProductListItemButtonsProps = {
    allowKeyboardFocus: boolean;
    isProductInComparison: boolean;
    isProductInWishlist: boolean;
    productName: string;
    toggleProductInComparison: () => void;
    toggleProductInWishlist: () => void;
};

export const ProductListItemButtons: FC<ProductListItemButtonsProps> = ({
    allowKeyboardFocus,
    isProductInComparison,
    isProductInWishlist,
    productName,
    toggleProductInComparison,
    toggleProductInWishlist,
}) => (
    <>
        <ProductCompareButton
            isProductInComparison={isProductInComparison}
            productName={productName}
            tabIndex={allowKeyboardFocus ? 0 : -1}
            toggleProductInComparison={toggleProductInComparison}
        />
        <ProductWishlistButton
            isProductInWishlist={isProductInWishlist}
            productName={productName}
            tabIndex={allowKeyboardFocus ? 0 : -1}
            toggleProductInWishlist={toggleProductInWishlist}
        />
    </>
);

type ProductListItemAddToCartProps = {
    allowKeyboardFocus: boolean;
    buttonSize?: 'small' | 'medium' | 'large' | 'xlarge';
    currentCart: Pick<CurrentCartType, 'cart' | 'isCartFetchingOrUnavailable'>;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    listIndex: number;
    product: TypeListedProductFragment;
    productActionClassName: string;
    shouldShowProductActionSkeleton: boolean;
    skeletonClassName: string;
};

export const ProductListItemAddToCart: FC<ProductListItemAddToCartProps> = ({
    allowKeyboardFocus,
    buttonSize,
    currentCart,
    gtmMessageOrigin,
    gtmProductListName,
    listIndex,
    product,
    productActionClassName,
    shouldShowProductActionSkeleton,
    skeletonClassName,
}) =>
    shouldShowProductActionSkeleton ? (
        <ProductActionSkeleton className={skeletonClassName} isWithAddToCart isWithProductListButtons={false} />
    ) : (
        <div className={productActionClassName}>
            <ProductAction
                buttonSize={buttonSize}
                gtmMessageOrigin={gtmMessageOrigin}
                gtmProductListName={gtmProductListName}
                listIndex={listIndex}
                product={product}
                currentCart={currentCart}
                skipKeyboardNavigation={!allowKeyboardFocus}
            />
        </div>
    );

export type ProductListItemActionProps = {
    allowKeyboardFocus: boolean;
    currentCart: Pick<CurrentCartType, 'cart' | 'isCartFetchingOrUnavailable'>;
    gtmMessageOrigin: GtmMessageOriginType;
    gtmProductListName: GtmProductListNameType;
    isProductInComparison: boolean;
    isProductInWishlist: boolean;
    listIndex: number;
    product: TypeListedProductFragment;
    shouldShowProductActionSkeleton: boolean;
    toggleProductInComparison: () => void;
    toggleProductInWishlist: () => void;
    visibleItemsConfig: ProductVisibleItemsConfigType;
};

export type ProductListItemLayoutProps = ProductListItemActionProps &
    Pick<ProductItemProps, 'className' | 'highlightBadgeText' | 'onClick'> & {
        onProductClick: () => void;
    };
