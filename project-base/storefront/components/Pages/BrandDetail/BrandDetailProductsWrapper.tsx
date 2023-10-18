import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { BrandDetailFragmentApi, BrandProductsQueryDocumentApi } from 'graphql/generated';
import { useGtmPaginatedProductListViewEvent } from 'gtm/hooks/productList/useGtmPaginatedProductListViewEvent';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { useProductsData } from 'helpers/loadMore';
import { getMappedProducts } from 'helpers/mappers/products';
import { RefObject } from 'react';

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
                fetching={fetching}
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={GtmProductListNameType.brand_detail}
                loadMoreFetching={loadMoreFetching}
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
