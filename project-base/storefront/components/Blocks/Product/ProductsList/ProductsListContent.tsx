import { ProductListItem } from './ProductListItem';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { ListedProductFragmentApi } from 'graphql/generated';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { useComparison } from 'hooks/productLists/comparison/useComparison';
import { useWishlist } from 'hooks/productLists/wishlist/useWishlist';
import { useQueryParams } from 'hooks/useQueryParams';
import React, { RefObject } from 'react';
import { SwipeableHandlers } from 'react-swipeable';

type ProductsListProps = {
    products: ListedProductFragmentApi[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    ref?: RefObject<HTMLUListElement>;
    productRefs?: RefObject<HTMLLIElement>[];
    swipeHandlers?: SwipeableHandlers;
    className?: string;
    classNameProduct?: string;
    isWithSimpleCards?: boolean;
};

export const ProductsListContent: FC<ProductsListProps> = ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    productRefs,
    className,
    classNameProduct,
    ref,
    children,
    swipeHandlers,
    isWithSimpleCards,
}) => {
    const { currentPage } = useQueryParams();
    const { toggleProductInComparison, isProductInComparison } = useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();

    return (
        <ul className={className} ref={ref} {...swipeHandlers}>
            {products.map((product, index) => (
                <ProductListItem
                    key={product.uuid}
                    className={classNameProduct}
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    isProductInComparison={isProductInComparison(product.uuid)}
                    isProductInWishlist={isProductInWishlist(product.uuid)}
                    isSimpleCard={isWithSimpleCards}
                    listIndex={(currentPage - 1) * DEFAULT_PAGE_SIZE + index}
                    product={product}
                    ref={productRefs?.[index]}
                    toggleProductInComparison={() => toggleProductInComparison(product.uuid)}
                    toggleProductInWishlist={() => toggleProductInWishlist(product.uuid)}
                />
            ))}
            {children}
        </ul>
    );
};
