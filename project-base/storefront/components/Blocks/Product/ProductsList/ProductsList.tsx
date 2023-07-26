import { ProductItem } from './ProductItem';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { CategoryDetailFragmentApi, ListedProductFragmentApi } from 'graphql/generated';
import { createEmptyArray } from 'helpers/arrayUtils';
import { useQueryParams } from 'hooks/useQueryParams';
import { useWishlist } from 'hooks/useWishlist';
import React from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';
import { ProductItemSkeleton } from './ProductItemSkeleton';
import { CategoryDetailContentMessage } from 'components/Pages/CategoryDetail/CategoryDetailContentMessage';
import { useComparison } from 'hooks/comparison/useComparison';
import { ProductComparePopup } from '../ButtonsAction/ProductComparePopup';

type ProductsListProps = {
    products: ListedProductFragmentApi[] | undefined;
    gtmProductListName: GtmProductListNameType;
    gtmMessageOrigin: GtmMessageOriginType;
    fetching?: boolean;
    category?: CategoryDetailFragmentApi;
};

const TEST_IDENTIFIER = 'blocks-product-list';

export const ProductsList: FC<ProductsListProps> = ({
    products,
    gtmProductListName,
    fetching,
    category,
    gtmMessageOrigin = GtmMessageOriginType.other,
}) => {
    const { currentPage } = useQueryParams();
    const { isPopupCompareOpen, toggleProductInComparison, setIsPopupCompareOpen, isProductInComparison } =
        useComparison();
    const { toggleProductInWishlist, isProductInWishlist } = useWishlist();

    if (!products?.length && !fetching) {
        return <CategoryDetailContentMessage />;
    }

    return (
        <div
            className="relative mb-5 grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-x-2 gap-y-6 pt-6"
            data-testid={TEST_IDENTIFIER}
        >
            {!!products?.length && !fetching ? (
                <>
                    {products.map((product, index) => (
                        <ProductItem
                            key={product.uuid}
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

                    {category && (
                        <Adverts
                            positionName="productListSecondRow"
                            currentCategory={category}
                            className="col-span-full row-start-2 mx-auto justify-center pl-2"
                            isSingle
                        />
                    )}

                    <ProductComparePopup
                        isVisible={isPopupCompareOpen}
                        onCloseCallback={() => setIsPopupCompareOpen(false)}
                    />
                </>
            ) : (
                createEmptyArray(DEFAULT_PAGE_SIZE).map((_, index) => <ProductItemSkeleton key={index} />)
            )}
        </div>
    );
};
