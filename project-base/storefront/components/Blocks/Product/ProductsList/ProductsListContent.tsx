import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { RefObject } from 'react';
import { SwipeableHandlers } from 'react-swipeable';
import { useComparison } from 'utils/productLists/comparison/useComparison';
import { useWishlist } from 'utils/productLists/wishlist/useWishlist';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { ProductItemProps, ProductListItem } from './ProductListItem';

type ProductsListProps = {
    products: TypeListedProductFragment[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    ref?: RefObject<HTMLUListElement | null>;
    productRefs?: RefObject<HTMLLIElement | null>[];
    swipeHandlers?: SwipeableHandlers;
    className?: string;
    isWithSimpleCards?: boolean;
    productItemProps?: Partial<ProductItemProps>;
    keyboardFocusableProductIndices?: number[];
    highlightFirstItemBadgeText?: string;
};

export const ProductsListContent: FC<ProductsListProps> = ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    productRefs,
    ref,
    children,
    swipeHandlers,
    productItemProps,
    className,
    keyboardFocusableProductIndices,
    highlightFirstItemBadgeText,
}) => {
    const currentPage = useCurrentPageQuery();
    const { toggleProductInComparison, isProductInComparison } = useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();

    return (
        <ul className={className} ref={ref} {...swipeHandlers}>
            {products.map((product, index) => (
                <ProductListItem
                    key={product.uuid}
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    isProductInComparison={isProductInComparison(product.uuid)}
                    isProductInWishlist={isProductInWishlist(product.uuid)}
                    listIndex={(currentPage - 1) * DEFAULT_PAGE_SIZE + index}
                    product={product}
                    ref={productRefs?.[index]}
                    toggleProductInComparison={() =>
                        toggleProductInComparison(
                            product,
                            gtmProductListName,
                            (currentPage - 1) * DEFAULT_PAGE_SIZE + index,
                        )
                    }
                    toggleProductInWishlist={() =>
                        toggleProductInWishlist(
                            product,
                            gtmProductListName,
                            (currentPage - 1) * DEFAULT_PAGE_SIZE + index,
                        )
                    }
                    allowKeyboardFocus={
                        !keyboardFocusableProductIndices || keyboardFocusableProductIndices.includes(index)
                    }
                    highlightBadgeText={
                        highlightFirstItemBadgeText && index === 0 && products.length > 1
                            ? highlightFirstItemBadgeText
                            : undefined
                    }
                    {...productItemProps}
                />
            ))}
            {children}
        </ul>
    );
};
