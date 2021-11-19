import { ProductImagesListFragmentApi, SearchQueryApi, useSearchQueryApi } from 'graphql/generated';
import { useEffect, useState } from 'react';
import { mapImageSizeApiData } from 'connectors/image/size/ImageSize';
import { mapProductPriceApiData } from 'connectors/products/Products';
import { SearchType } from './types';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import { useShopsysSelector } from 'redux/main';

export const getSearch = (searchQuery: string): SearchType | undefined => {
    const [result] = useSearchQueryApi({
        variables: { search: searchQuery },
        pause: searchQuery.length < 3,
        requestPolicy: 'network-only',
    });
    const [mappedResult, setMappedResult] = useState<SearchType | undefined>(undefined);
    const { currencyCode } = useShopsysSelector((state) => state.domain);

    useQueryError(result.error);
    useEffect(() => {
        if (result.data !== undefined) {
            setMappedResult(mapSearchResult(result.data, currencyCode));
        }
    }, [result.data]);
    useEffect(() => {
        if (searchQuery.length < 3 && mappedResult !== undefined) {
            setMappedResult(undefined);
        }
    }, [searchQuery]);

    return mappedResult;
};

const mapSearchResult = (apiData: SearchQueryApi, currencyCode: string): SearchType => {
    return {
        ...apiData,
        articlesSearch: mapArticlesSearchResults(apiData.articlesSearch),
        categoriesSearch: mapCategoriesSearchResults(apiData.categoriesSearch),
        productsSearch: mapProductsSearchResults(apiData.productsSearch, currencyCode),
    };
};

const mapArticlesSearchResults = (apiData: SearchQueryApi['articlesSearch']): SearchType['articlesSearch'] => {
    const mappedArticles = [];

    if (apiData !== undefined && apiData !== null) {
        for (const article of apiData) {
            if (article !== undefined && article !== null) {
                mappedArticles.push(article);
            }
        }
    }
    return mappedArticles;
};

const mapCategoriesSearchResults = (apiData: SearchQueryApi['categoriesSearch']): SearchType['categoriesSearch'] => {
    const mappedCategories = [];

    if (apiData?.edges !== undefined && apiData?.edges !== null) {
        for (const categoryEdge of apiData.edges) {
            if (categoryEdge?.node !== undefined && categoryEdge?.node !== null) {
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
    apiData: SearchQueryApi['productsSearch'],
    currencyCode: string,
): SearchType['productsSearch'] => {
    const mappedProducts = [];

    if (apiData?.edges !== undefined && apiData.edges !== null) {
        for (const productEdge of apiData.edges) {
            if (productEdge?.node !== undefined && productEdge?.node !== null) {
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
