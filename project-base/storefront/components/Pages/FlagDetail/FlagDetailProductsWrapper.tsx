import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { useGtmPaginatedProductListViewEvent } from 'gtm/hooks/productList/useGtmPaginatedProductListViewEvent';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { getMappedProducts } from 'helpers/mappers/products';
import { useProductsData } from 'helpers/loadMore';
import { RefObject } from 'react';
import { FlagDetailFragmentApi } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { FlagProductsQueryDocumentApi } from 'graphql/requests/products/queries/FlagProductsQuery.generated';

type FlagDetailProductsWrapperProps = {
    flag: FlagDetailFragmentApi;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const FlagDetailProductsWrapper: FC<FlagDetailProductsWrapperProps> = ({ flag, paginationScrollTargetRef }) => {
    const [flagProductsData, hasNextPage, fetching, loadMoreFetching] = useProductsData(
        FlagProductsQueryDocumentApi,
        flag.products.totalCount,
    );
    const flagListedProducts = getMappedProducts(flagProductsData);

    useGtmPaginatedProductListViewEvent(flagListedProducts, GtmProductListNameType.flag_detail);

    return (
        <>
            <ProductsList
                gtmProductListName={GtmProductListNameType.flag_detail}
                fetching={fetching}
                loadMoreFetching={loadMoreFetching}
                products={flagListedProducts}
                gtmMessageOrigin={GtmMessageOriginType.other}
            />
            <Pagination
                totalCount={flag.products.totalCount}
                paginationScrollTargetRef={paginationScrollTargetRef}
                isWithLoadMore
                hasNextPage={hasNextPage}
            />
        </>
    );
};
