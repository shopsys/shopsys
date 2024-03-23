import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { FlagDetailFragment } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { FlagProductsQueryDocument } from 'graphql/requests/products/queries/FlagProductsQuery.generated';
import { useGtmPaginatedProductListViewEvent } from 'gtm/hooks/productList/useGtmPaginatedProductListViewEvent';
import { GtmMessageOriginType, GtmProductListNameType } from 'gtm/types/enums';
import { useProductsData } from 'helpers/loadMore';
import { getMappedProducts } from 'helpers/mappers/products';
import { RefObject } from 'react';

type FlagDetailProductsWrapperProps = {
    flag: FlagDetailFragment;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const FlagDetailProductsWrapper: FC<FlagDetailProductsWrapperProps> = ({ flag, paginationScrollTargetRef }) => {
    const [flagProductsData, hasNextPage, fetching, loadMoreFetching] = useProductsData(
        FlagProductsQueryDocument,
        flag.products.totalCount,
    );
    const flagListedProducts = getMappedProducts(flagProductsData);

    useGtmPaginatedProductListViewEvent(flagListedProducts, GtmProductListNameType.flag_detail);

    return (
        <>
            <ProductsList
                fetching={fetching}
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={GtmProductListNameType.flag_detail}
                loadMoreFetching={loadMoreFetching}
                products={flagListedProducts}
            />
            <Pagination
                isWithLoadMore
                hasNextPage={hasNextPage}
                paginationScrollTargetRef={paginationScrollTargetRef}
                totalCount={flag.products.totalCount}
            />
        </>
    );
};
