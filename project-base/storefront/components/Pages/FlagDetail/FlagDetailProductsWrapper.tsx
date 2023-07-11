import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { getEndCursor } from 'components/Blocks/Product/Filter/helpers/getEndCursor';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { FlagDetailFragmentApi, useFlagProductsQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { getMappedProducts } from 'helpers/mappers/products';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';

import { useGtmPaginatedProductListViewEvent } from 'hooks/gtm/productList/useGtmPaginatedProductListViewEvent';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { RefObject } from 'react';
import { GtmMessageOriginType, GtmProductListNameType } from 'types/gtm/enums';
import { getSlugFromUrl } from 'utils/getSlugFromUrl';

type FlagDetailProductsWrapperProps = {
    flag: FlagDetailFragmentApi;
    paginationScrollTargetRef: RefObject<HTMLDivElement>;
};

export const FlagDetailProductsWrapper: FC<FlagDetailProductsWrapperProps> = ({ flag, paginationScrollTargetRef }) => {
    const { query, asPath } = useRouter();
    const { currentPage } = useQueryParams();
    const orderingMode = getProductListSort(parseProductListSortFromQuery(query.sort));
    const parametersFilter = getFilterOptions(parseFilterOptionsFromQuery(query.filter));

    const [{ data: flagProductsData, fetching }] = useFlagProductsQueryApi({
        variables: {
            endCursor: getEndCursor(currentPage),
            filter: mapParametersFilter(parametersFilter),
            orderingMode,
            urlSlug: getSlugFromUrl(getUrlWithoutGetParameters(asPath)),
            pageSize: DEFAULT_PAGE_SIZE,
        },
    });

    const flagListedProducts = getMappedProducts(flagProductsData?.products.edges);

    useGtmPaginatedProductListViewEvent(flagListedProducts, GtmProductListNameType.flag_detail);

    return (
        <>
            <ProductsList
                gtmProductListName={GtmProductListNameType.flag_detail}
                fetching={fetching}
                products={flagListedProducts}
                gtmMessageOrigin={GtmMessageOriginType.other}
            />
            <Pagination totalCount={flag.products.totalCount} paginationScrollTargetRef={paginationScrollTargetRef} />
        </>
    );
};
