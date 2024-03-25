import { ProductListItem } from './ProductListItem';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { ListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useComparison } from 'hooks/productLists/comparison/useComparison';
import { useWishlist } from 'hooks/productLists/wishlist/useWishlist';
import { useCurrentPageQuery } from 'hooks/queryParams/useCurrentPageQuery';
import dynamic from 'next/dynamic';
import React, { RefObject } from 'react';
import { SwipeableHandlers } from 'react-swipeable';

const ProductComparePopup = dynamic(() =>
    import('../ButtonsAction/ProductComparePopup').then((component) => component.ProductComparePopup),
);

type ProductsListProps = {
    products: ListedProductFragment[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    ref?: RefObject<HTMLUListElement>;
    productRefs?: RefObject<HTMLLIElement>[];
    swipeHandlers?: SwipeableHandlers;
    className?: string;
    classNameProduct?: string;
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
}) => {
    const currentPage = useCurrentPageQuery();
    const { isPopupCompareOpen, toggleProductInComparison, setIsPopupCompareOpen, isProductInComparison } =
        useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();

    return (
        <>
            <ul className={className} ref={ref} {...swipeHandlers}>
                {products.map((product, index) => (
                    <ProductListItem
                        key={product.uuid}
                        className={classNameProduct}
                        gtmMessageOrigin={gtmMessageOrigin}
                        gtmProductListName={gtmProductListName}
                        isProductInComparison={isProductInComparison(product.uuid)}
                        isProductInWishlist={isProductInWishlist(product.uuid)}
                        listIndex={(currentPage - 1) * DEFAULT_PAGE_SIZE + index}
                        product={product}
                        ref={productRefs?.[index]}
                        toggleProductInComparison={() => toggleProductInComparison(product.uuid)}
                        toggleProductInWishlist={() => toggleProductInWishlist(product.uuid)}
                    />
                ))}
                {children}
            </ul>

            {isPopupCompareOpen && <ProductComparePopup onCloseCallback={() => setIsPopupCompareOpen(false)} />}
        </>
    );
};
