import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { FlagDetailFragment } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { FlagProductsQueryDocument } from 'graphql/requests/products/queries/FlagProductsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmPaginatedProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmPaginatedProductListViewEvent';
import { RefObject } from 'react';
import { useProductsData } from 'utils/loadMore/useProductsData';
import { getMappedProducts } from 'utils/mappers/products';

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
