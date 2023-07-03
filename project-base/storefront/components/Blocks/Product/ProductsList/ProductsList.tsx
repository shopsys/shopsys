import { ProductItem } from './ProductItem';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { CategoryDetailFragmentApi, ListedProductFragmentApi } from 'graphql/generated';
import { createEmptyArray } from 'helpers/arrayUtils';
import { useQueryParams } from 'hooks/useQueryParams';
import React from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';
import { ProductItemSkeleton } from './ProductItemSkeleton';
import { CategoryDetailContentMessage } from 'components/Pages/CategoryDetail/CategoryDetailContentMessage';

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
                    {products.map((listedProductItem, index) => (
                        <ProductItem
                            key={listedProductItem.uuid}
                            product={listedProductItem}
                            listIndex={(currentPage - 1) * DEFAULT_PAGE_SIZE + index}
                            gtmProductListName={gtmProductListName}
                            gtmMessageOrigin={gtmMessageOrigin}
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
                </>
            ) : (
                createEmptyArray(10).map((_, index) => <ProductItemSkeleton key={index} />)
            )}
        </div>
    );
};
