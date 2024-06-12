import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { TypeFlagDetailFragment } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { FlagProductsQueryDocument } from 'graphql/requests/products/queries/FlagProductsQuery.generated';
import { GtmMessageOriginType } from 'gtm/enums/GtmMessageOriginType';
import { GtmProductListNameType } from 'gtm/enums/GtmProductListNameType';
import { useGtmPaginatedProductListViewEvent } from 'gtm/utils/pageViewEvents/productList/useGtmPaginatedProductListViewEvent';
import { RefObject } from 'react';
import { useProductsData } from 'utils/loadMore/useProductsData';
import { getMappedProducts } from 'utils/mappers/products';

type FlagDetailProductsWrapperProps = {
    flag: TypeFlagDetailFragment;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const FlagDetailProductsWrapper: FC<FlagDetailProductsWrapperProps> = ({ flag, paginationScrollTargetRef }) => {
    const { products, areProductsFetching, hasNextPage, isLoadingMoreProducts } = useProductsData(
        FlagProductsQueryDocument,
        flag.products.totalCount,
    );
    const flagListedProducts = getMappedProducts(products);

    useGtmPaginatedProductListViewEvent(flagListedProducts, GtmProductListNameType.flag_detail);

    return (
        <>
            <ProductsList
                areProductsFetching={areProductsFetching}
                gtmMessageOrigin={GtmMessageOriginType.other}
                gtmProductListName={GtmProductListNameType.flag_detail}
                isLoadingMoreProducts={isLoadingMoreProducts}
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
