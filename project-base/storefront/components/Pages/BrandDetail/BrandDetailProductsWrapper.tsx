import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { BrandDetailFragmentApi, BrandProductsQueryDocumentApi } from 'graphql/generated';
import { getMappedProducts } from 'helpers/mappers/products';
import { useProductsData } from 'helpers/pagination/loadMore';

import { useGtmPaginatedProductListViewEvent } from 'hooks/gtm/productList/useGtmPaginatedProductListViewEvent';
import { RefObject } from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';

type BrandDetailProductsWrapperProps = {
    brand: BrandDetailFragmentApi;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const BrandDetailProductsWrapper: FC<BrandDetailProductsWrapperProps> = ({
    brand,
    paginationScrollTargetRef,
}) => {
    const [brandProductsData, hasNextPage, fetching, loadMoreFetching] = useProductsData(
        BrandProductsQueryDocumentApi,
        brand.products.totalCount,
    );
    const listedBrandProducts = getMappedProducts(brandProductsData);

    useGtmPaginatedProductListViewEvent(listedBrandProducts, GtmProductListNameType.brand_detail);

    return (
        <>
            <ProductsList
                gtmProductListName={GtmProductListNameType.brand_detail}
                fetching={fetching}
                loadMoreFetching={loadMoreFetching}
                products={listedBrandProducts}
                gtmMessageOrigin={GtmMessageOriginType.other}
            />
            <Pagination
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={brand.products.totalCount}
                isWithLoadMore
                hasNextPage={hasNextPage}
            />
        </>
    );
};
