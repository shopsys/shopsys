import { CategoryDetailContentMessage } from '../CategoryDetail/CategoryDetailContentMessage';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { mapListedProductConnectionType } from 'connectors/products/Products';
import { useBrandProductsQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useGtmBrandProductListView } from 'hooks/gtm/useGtmBrandProductListView';
import { useListingForPagination } from 'hooks/ui/useListingForPagination';
import { useRouter } from 'next/router';
import React, { RefObject } from 'react';
import { useShopsysSelector } from 'redux/main';
import { BrandDetailType } from 'types/brand';
import { ListedProductType } from 'types/product';

type BrandDetailProductsWrapperProps = {
    brand: BrandDetailType;
    containerWrapRef: RefObject<HTMLDivElement>;
};

export const BrandDetailProductsWrapper: FC<BrandDetailProductsWrapperProps> = ({ brand, containerWrapRef }) => {
    const { asPath, query } = useRouter();
    const [{ endCursor }] = usePaginationContext();
    const orderingMode = getProductListSort(parseProductListSortFromQuery(query.sort));
    const parametersFilter = getFilterOptions(parseFilterOptionsFromQuery(query.filter));
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    const [{ data, fetching }] = useBrandProductsQueryApi({
        variables: {
            endCursor: endCursor ?? '',
            filter: mapParametersFilter(parametersFilter),
            orderingMode,
            uuid: brand.uuid,
            pageSize: DEFAULT_PAGE_SIZE,
        },
    });

    const [dataItems] = useListingForPagination<ListedProductType>(
        data?.brand?.products !== undefined
            ? mapListedProductConnectionType(data.brand.products, currencyCode).products
            : [],
    );

    useGtmBrandProductListView(brand, getUrlWithoutGetParameters(asPath), dataItems, fetching);

    return (
        <>
            {dataItems.length !== 0 ? (
                <>
                    <ProductsList gtmListName="brand" fetching={fetching} products={dataItems} />
                    <Pagination containerWrapRef={containerWrapRef} totalCount={brand.productConnection.totalCount} />
                </>
            ) : (
                <CategoryDetailContentMessage />
            )}
        </>
    );
};
