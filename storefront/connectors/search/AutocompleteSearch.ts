import {
    AutocompleteSearchQueryApi,
    ProductImagesListFragmentApi,
    useAutocompleteSearchQueryApi,
} from 'graphql/generated';
import { useEffect, useState } from 'react';
import { AutocompleteSearchType } from 'types/search';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { mapProductPriceApiData } from 'connectors/products/Products';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';
import { mapSimpleArticleApiData } from 'connectors/articles/Articles';

export const getAutocompleteSearch = (autocompleteSearch: string): AutocompleteSearchType | undefined => {
    const [result] = useAutocompleteSearchQueryApi({
        variables: { search: autocompleteSearch },
        pause: autocompleteSearch.length < 3,
        requestPolicy: 'network-only',
    });
    const [mappedResult, setMappedResult] = useState<AutocompleteSearchType | undefined>(undefined);
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    useQueryError(result.error);
    useEffect(() => {
        if (result.data !== undefined) {
            setMappedResult(mapSearchResult(result.data, currencyCode));
        }
    }, [result.data]);
    useEffect(() => {
        if (autocompleteSearch.length < 3 && mappedResult !== undefined) {
            setMappedResult(undefined);
        }
    }, [autocompleteSearch]);

    return mappedResult;
};

const mapSearchResult = (apiData: AutocompleteSearchQueryApi, currencyCode: string): AutocompleteSearchType => {
    return {
        ...apiData,
        articlesSearch: mapArticlesSearchResults(apiData.articlesSearch),
        categoriesSearch: mapCategoriesSearchResults(apiData.categoriesSearch),
        productsSearch: mapProductsSearchResults(apiData.productsSearch, currencyCode),
    };
};

const mapArticlesSearchResults = (
    apiData: AutocompleteSearchQueryApi['articlesSearch'],
): AutocompleteSearchType['articlesSearch'] => {
    const mappedArticles = [];

    if (apiData !== undefined && apiData !== null) {
        for (const article of apiData) {
            if (article !== undefined && article !== null) {
                mappedArticles.push(mapSimpleArticleApiData(article));
            }
        }
    }
    return mappedArticles;
};

const mapCategoriesSearchResults = (
    apiData: AutocompleteSearchQueryApi['categoriesSearch'],
): AutocompleteSearchType['categoriesSearch'] => {
    const mappedCategories = [];

    if (apiData?.edges !== undefined && apiData.edges !== null) {
        for (const categoryEdge of apiData.edges) {
            if (categoryEdge?.node !== undefined && categoryEdge.node !== null) {
                mappedCategories.push({
                    ...categoryEdge.node,
                    name: categoryEdge.node.name,
                });
            }
        }
    }
    return { totalCount: apiData?.totalCount === undefined ? 0 : apiData.totalCount, categories: mappedCategories };
};

const mapProductsSearchResults = (
    apiData: AutocompleteSearchQueryApi['productsSearch'],
    currencyCode: string,
): AutocompleteSearchType['productsSearch'] => {
    const mappedProducts = [];

    if (apiData?.edges !== undefined && apiData.edges !== null) {
        for (const productEdge of apiData.edges) {
            if (productEdge?.node !== undefined && productEdge.node !== null) {
                mappedProducts.push({
                    ...productEdge.node,
                    name: productEdge.node.name,
                    price: mapProductPriceApiData(productEdge.node.price, currencyCode),
                    image: mapProductsSearchResultImage(productEdge.node.images),
                });
            }
        }
    }

    return { totalCount: apiData?.totalCount === undefined ? 0 : apiData.totalCount, products: mappedProducts };
};

const mapProductsSearchResultImage = (apiData: ProductImagesListFragmentApi['images']) => {
    if (!(0 in apiData)) {
        return null;
    }
    return mapImageSizeApiData(apiData[0].sizes[0]);
};
