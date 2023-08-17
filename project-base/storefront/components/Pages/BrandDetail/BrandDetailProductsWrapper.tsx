import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { getMappedProducts } from 'helpers/mappers/products';
import { useProductsData } from 'helpers/loadMore';
import { useGtmPaginatedProductListViewEvent } from 'gtm/hooks/productList/useGtmPaginatedProductListViewEvent';
import { RefObject } from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { BrandDetailFragmentApi } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { BrandProductsQueryDocumentApi } from 'graphql/requests/products/queries/BrandProductsQuery.generated';

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
