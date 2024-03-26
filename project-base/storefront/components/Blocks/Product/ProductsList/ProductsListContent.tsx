import { ProductListItem } from './ProductListItem';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useCurrentPage } from 'hooks/queryParams/useCurrentPage';
import React, { RefObject } from 'react';
import { SwipeableHandlers } from 'react-swipeable';

type ProductsListProps = {
    products: TypeListedProductFragment[];
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
    const currentPage = useCurrentPage();

    return (
        <ul className={className} ref={ref} {...swipeHandlers}>
            {products.map((product, index) => (
                <ProductListItem
                    key={product.uuid}
                    className={classNameProduct}
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    listIndex={(currentPage - 1) * DEFAULT_PAGE_SIZE + index}
                    product={product}
                    ref={productRefs?.[index]}
                />
            ))}
            {children}
        </ul>
    );
};
