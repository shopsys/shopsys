import { ProductItem } from './ProductItem';
import { LoaderWithOverlay } from 'components/Basic/Loader/LoaderWithOverlay';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { CategoryDetailFragmentApi, ListedProductFragmentApi } from 'graphql/generated';
import { useQueryParams } from 'hooks/useQueryParams';
import React from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';

type ProductsListProps = {
    products: ListedProductFragmentApi[];
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

    return (
        <div
            className="relative mb-5 grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] gap-x-2 gap-y-6 pt-6"
            data-testid={TEST_IDENTIFIER}
        >
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
            {fetching && <LoaderWithOverlay className="w-20" />}
        </div>
    );
};
