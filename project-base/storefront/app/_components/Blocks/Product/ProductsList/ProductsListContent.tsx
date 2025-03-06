import { ProductItemProps, ProductListItem } from './ProductListItem';
import { DEFAULT_PAGE_SIZE } from 'config/constants';
import { TypeListedProductFragment } from 'graphql/requests/products/fragments/ListedProductFragment.ssr';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import React, { RefObject } from 'react';
import { SwipeableHandlers } from 'react-swipeable';

type ProductsListProps = {
    products: TypeListedProductFragment[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    productRefs?: RefObject<HTMLLIElement>[];
    swipeHandlers?: SwipeableHandlers;
    className?: string;
    isWithSimpleCards?: boolean;
    productItemProps?: Partial<ProductItemProps>;
};

export const ProductsListContent: FC<ProductsListProps> = async ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    productRefs,
    children,
    swipeHandlers,
    productItemProps,
    className,
}) => {
    // TODO: current page hook
    // const currentPage = useCurrentPageQuery();
    const currentPage = 1;

    return (
        <ul className={className} {...swipeHandlers}>
            {products.map((product, index) => (
                <ProductListItem
                    key={product.uuid}
                    gtmMessageOrigin={gtmMessageOrigin}
                    gtmProductListName={gtmProductListName}
                    listIndex={(currentPage - 1) * DEFAULT_PAGE_SIZE + index}
                    product={product}
                    ref={productRefs?.[index]}
                    {...productItemProps}
                />
            ))}
            {children}
        </ul>
    );
};
