import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { TypeBrandDetailFragment } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { BrandProductsQueryDocument } from 'graphql/requests/products/queries/BrandProductsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmPaginatedProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmPaginatedProductListViewEvent';
import { RefObject } from 'react';
import { useProductsData } from 'utils/loadMore/useProductsData';
import { getMappedProducts } from 'utils/mappers/products';

type BrandDetailProductsWrapperProps = {
    brand: TypeBrandDetailFragment;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const BrandDetailProductsWrapper: FC<BrandDetailProductsWrapperProps> = ({
    brand,
    paginationScrollTargetRef,
}) => {
    const [brandProductsData, hasNextPage, fetching, loadMoreFetching] = useProductsData(
        BrandProductsQueryDocument,
        brand.products.totalCount,
    );
    const listedBrandProducts = getMappedProducts(brandProductsData);

    useGtmPaginatedProductListViewEvent(listedBrandProducts, GtmProductListNameType.brand_detail);

    return (
        <>
            <ProductsList
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={GtmProductListNameType.brand_detail}
                isFetching={fetching}
                isLoadMoreFetching={loadMoreFetching}
                products={listedBrandProducts}
            />
            <Pagination
                isWithLoadMore
                hasNextPage={hasNextPage}
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={brand.products.totalCount}
            />
        </>
    );
};
