import { CategoryDetailContentMessage } from './CategoryDetailContentMessage';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { mapListedProductConnectionType } from 'connectors/products/Products';
import { useCategoryProductsQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { getCategoryOrSeoCategoryGtmListName } from 'helpers/gtm/gtm';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useGtmCategoryProductListView } from 'hooks/gtm/useGtmCategoryProductListView';
import { useListingForPagination } from 'hooks/ui/useListingForPagination';
import { useRouter } from 'next/router';
import React, { RefObject, useMemo } from 'react';
import { useShopsysSelector } from 'redux/main';
import { CategoryDetailType } from 'types/category';
import { ListedProductType } from 'types/product';

type CategoryDetailProps = {
    category: CategoryDetailType;
    containerWrapRef: RefObject<HTMLDivElement>;
};

export const CategoryDetailProductsWrapper: FC<CategoryDetailProps> = ({ category, containerWrapRef }) => {
    const { asPath, query } = useRouter();
    const [{ endCursor }] = usePaginationContext();
    const orderingMode = getProductListSort(parseProductListSortFromQuery(query.sort));
    const parametersFilter = getFilterOptions(parseFilterOptionsFromQuery(query.filter));
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    const [{ data, fetching }] = useCategoryProductsQueryApi({
        variables: {
            endCursor: endCursor ?? '',
            filter: mapParametersFilter(parametersFilter),
            orderingMode,
            uuid: category.uuid,
            pageSize: DEFAULT_PAGE_SIZE,
        },
    });

    const gtmListName = useMemo(() => getCategoryOrSeoCategoryGtmListName(category.originalCategorySlug), [category]);
    const [dataItems] = useListingForPagination<ListedProductType>(
        data?.category?.products !== undefined
            ? mapListedProductConnectionType(data.category.products, currencyCode).products
            : [],
    );

    useGtmCategoryProductListView(category, getUrlWithoutGetParameters(asPath), dataItems, fetching);

    return (
        <>
            {dataItems.length !== 0 ? (
                <>
                    <ProductsList gtmListName={gtmListName} products={dataItems} fetching={fetching} />
                    <Pagination
                        containerWrapRef={containerWrapRef}
                        totalCount={category.productConnection.totalCount}
                    />
                </>
            ) : (
                <CategoryDetailContentMessage />
            )}
        </>
    );
};
