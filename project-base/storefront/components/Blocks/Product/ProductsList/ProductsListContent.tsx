import { ProductListItem } from './ProductListItem';
import { ListedProductFragmentApi } from 'graphql/generated';
import { useWishlist } from 'hooks/useWishlist';
import React, { RefObject } from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';
import { useComparison } from 'hooks/comparison/useComparison';
import dynamic from 'next/dynamic';
import { useQueryParams } from 'hooks/useQueryParams';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';

const ProductComparePopup = dynamic(() =>
    import('../ButtonsAction/ProductComparePopup').then((component) => component.ProductComparePopup),
);

type ProductsListProps = {
    products: ListedProductFragmentApi[];
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    productRefs?: RefObject<HTMLDivElement>[];
};

export const ProductsListContent: FC<ProductsListProps> = ({
    products,
    gtmProductListName,
    gtmMessageOrigin = GtmMessageOriginType.other,
    productRefs,
    className,
}) => {
    const { currentPage } = useQueryParams();
    const { isPopupCompareOpen, toggleProductInComparison, setIsPopupCompareOpen, isProductInComparison } =
        useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();

    return (
        <>
            {products.map((product, index) => (
                <ProductListItem
                    ref={productRefs?.[index]}
                    key={product.uuid}
                    className={className}
                    product={product}
                    listIndex={(currentPage - 1) * DEFAULT_PAGE_SIZE + index}
                    gtmProductListName={gtmProductListName}
                    gtmMessageOrigin={gtmMessageOrigin}
                    isProductInComparison={isProductInComparison(product.uuid)}
                    toggleProductInComparison={() => toggleProductInComparison(product.uuid)}
                    isProductInWishlist={isProductInWishlist(product.uuid)}
                    toggleProductInWishlist={() => toggleProductInWishlist(product.uuid)}
                />
            ))}

            {isPopupCompareOpen && <ProductComparePopup onCloseCallback={() => setIsPopupCompareOpen(false)} />}
        </>
    );
};
