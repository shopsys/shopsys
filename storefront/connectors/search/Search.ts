import { DEFAULT_PAGE_SIZE } from 'components/Blocks/Pagination/Pagination';
import { mapSimpleArticlesInterface } from 'connectors/articleInterface/ArticleInterface';
import { mapListedBrandsApiData } from 'connectors/brands/Brands';
import { mapListedCategoryConnectionApiData } from 'connectors/categories/Categories';
import { mapListedProductConnectionPreviewType } from 'connectors/products/Products';
import { ProductOrderingModeEnumApi, SearchQueryApi, useSearchQueryApi } from 'graphql/generated';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { isServer } from 'helpers/misc/isServer';
import { useQueryError } from 'hooks/graphQl/useQueryError';
import { useEffect, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { FilterOptionsUrlQueryType } from 'types/productFilter';
import { SearchType } from 'types/search';

export const useSearch = (
    searchQuery: string,
    searchProductsSort: ProductOrderingModeEnumApi | null,
    optionsFilter: FilterOptionsUrlQueryType | null,
): SearchType | undefined => {
    const [result] = useSearchQueryApi({
        variables: {
            search: searchQuery,
            orderingMode: searchProductsSort,
            filter: mapParametersFilter(optionsFilter),
            pageSize: DEFAULT_PAGE_SIZE,
        },
    });
    const { currencyCode } = useShopsysSelector((state) => state.domain);
    const [mappedResult, setMappedResult] = useState<SearchType | undefined>(
        mapSearchResult(result.data, currencyCode),
    );

    useQueryError(result.error);
    useEffect(() => {
        if (!result.stale) {
            setMappedResult(mapSearchResult(result.data, currencyCode));
        }
    }, [currencyCode, result.data, result.stale]);

    if (isServer() && result.data !== undefined) {
        return mapSearchResult(result.data, currencyCode);
    }

    return mappedResult;
};

const mapSearchResult = (apiData: SearchQueryApi | undefined, currencyCode: string): SearchType | undefined => {
    if (apiData === undefined) {
        return undefined;
    }

    return {
        articlesSearch: mapSimpleArticlesInterface(apiData.articlesSearch),
        brandSearch: mapListedBrandsApiData(apiData.brandSearch),
        categoriesSearch: mapListedCategoryConnectionApiData(apiData.categoriesSearch),
        productsSearch: mapListedProductConnectionPreviewType(apiData.productsSearch, currencyCode),
    };
};
