import { CategoryDetailContentMessage } from '../CategoryDetail/CategoryDetailContentMessage';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { FlagDetailFragmentApi, ListedProductFragmentApi, useFlagProductsQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useGtmFlagProductListView } from 'hooks/gtm/useGtmFlagProductListView';
import { useListingForPagination } from 'hooks/ui/useListingForPagination';
import { useRouter } from 'next/router';
import { RefObject } from 'react';

type FlagDetailProductsWrapperProps = {
    flag: FlagDetailFragmentApi;
    containerWrapRef: RefObject<HTMLDivElement>;
};

export const FlagDetailProductsWrapper: FC<FlagDetailProductsWrapperProps> = ({ flag, containerWrapRef }) => {
    const { asPath, query } = useRouter();
    const [{ endCursor }] = usePaginationContext();
    const orderingMode = getProductListSort(parseProductListSortFromQuery(query.sort));
    const parametersFilter = getFilterOptions(parseFilterOptionsFromQuery(query.filter));

    const [{ data, fetching }] = useQueryError(
        useFlagProductsQueryApi({
            variables: {
                endCursor: endCursor ?? '',
                filter: mapParametersFilter(parametersFilter),
                orderingMode,
                uuid: flag.uuid,
                pageSize: DEFAULT_PAGE_SIZE,
            },
        }),
    );

    const [dataItems] = useListingForPagination<ListedProductFragmentApi>(data?.flag?.products.edges);

    useGtmFlagProductListView(flag, getUrlWithoutGetParameters(asPath), dataItems, fetching);

    return (
        <>
            {dataItems.length !== 0 ? (
                <>
                    <ProductsList gtmListName="flag" fetching={fetching} products={dataItems} />
                    <Pagination totalCount={flag.products.totalCount} containerWrapRef={containerWrapRef} />
                </>
            ) : (
                <CategoryDetailContentMessage />
            )}
        </>
    );
};
