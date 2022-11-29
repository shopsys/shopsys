import { CategoryDetailContentMessageStyled } from '../CategoryDetail/CategoryDetailContent.style';
import { DEFAULT_PAGE_SIZE, Pagination } from 'components/Blocks/Pagination/Pagination';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { ProductsList } from 'components/Blocks/Product/ProductsList/ProductsList';
import { mapListedProductConnectionType } from 'connectors/products/Products';
import { useFlagProductsQueryApi } from 'graphql/generated';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { getUrlWithoutGetParameters } from 'helpers/parsing/getUrlWithoutGetParameters';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useGtmFlagProductListView } from 'hooks/gtm/useGtmFlagProductListView';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useListingForPagination } from 'hooks/ui/useListingForPagination';
import Trans from 'next-translate/Trans';
import { useRouter } from 'next/router';
import React, { FC, RefObject } from 'react';
import { useShopsysSelector } from 'redux/main';
import { FlagDetailType } from 'types/flag';
import { ListedProductType } from 'types/product';

type FlagDetailProductsWrapperProps = {
    flag: FlagDetailType;
    containerWrapRef: RefObject<HTMLDivElement>;
};

export const FlagDetailProductsWrapper: FC<FlagDetailProductsWrapperProps> = ({ flag, containerWrapRef }) => {
    const t = useTypedTranslationFunction();
    const { asPath, query } = useRouter();
    const [{ endCursor }] = usePaginationContext();
    const orderingMode = getProductListSort(parseProductListSortFromQuery(query.sort));
    const parametersFilter = getFilterOptions(parseFilterOptionsFromQuery(query.filter));
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    const [{ data, fetching }] = useFlagProductsQueryApi({
        variables: {
            endCursor: endCursor ?? '',
            filter: mapParametersFilter(parametersFilter),
            orderingMode,
            uuid: flag.uuid,
            pageSize: DEFAULT_PAGE_SIZE,
        },
    });

    const [dataItems] = useListingForPagination<ListedProductType>(
        data?.flag?.products !== undefined
            ? mapListedProductConnectionType(data.flag.products, currencyCode).products
            : [],
    );

    useGtmFlagProductListView(flag, getUrlWithoutGetParameters(asPath), dataItems, fetching);

    return (
        <>
            {dataItems.length !== 0 ? (
                <>
                    <ProductsList gtmListName="flag" fetching={fetching} products={dataItems} />
                    <Pagination totalCount={flag.productConnection.totalCount} containerWrapRef={containerWrapRef} />
                </>
            ) : (
                <CategoryDetailContentMessageStyled>
                    <div>
                        <strong>{t('No results match the filter')}</strong>
                    </div>
                    <div>
                        <Trans i18nKey="ProductsNoResults" components={{ 0: <br /> }} />
                    </div>
                </CategoryDetailContentMessageStyled>
            )}
        </>
    );
};
