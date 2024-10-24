import { mergeItemEdges } from './mergeItemEdges';
import { readProductsFromCache } from './readProductsFromCache';
import { getEndCursor } from 'components/Blocks/Product/Filter/utils/getEndCursor';
import { DocumentNode } from 'graphql';
import { TypeListedProductConnectionFragment } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';
import { TypeProductOrderingModeEnum, Maybe, TypeProductFilter } from 'graphql/types';
import { Client } from 'urql';

export const getPreviousProductsFromCache = (
    queryDocument: DocumentNode,
    client: Client,
    urlSlug: string,
    sort: TypeProductOrderingModeEnum | null,
    filter: Maybe<TypeProductFilter>,
    pageSize: number,
    initialPageSize: number,
    currentPage: number,
    currentLoadMore: number,
    readProducts: typeof readProductsFromCache,
): TypeListedProductConnectionFragment['edges'] | undefined => {
    let cachedPartOfProducts: TypeListedProductConnectionFragment['edges'] | undefined;
    let iterationsCounter = currentLoadMore;

    if (initialPageSize !== pageSize) {
        const offsetEndCursor = getEndCursor(currentPage);
        const currentCacheSlice = readProductsFromCache(
            queryDocument,
            client,
            urlSlug,
            sort,
            filter,
            offsetEndCursor,
            initialPageSize,
        ).products;

        if (currentCacheSlice) {
            cachedPartOfProducts = currentCacheSlice;
            iterationsCounter -= initialPageSize / pageSize;
        } else {
            return undefined;
        }
    }

    while (iterationsCounter > 0) {
        const offsetEndCursor = getEndCursor(currentPage + currentLoadMore - iterationsCounter);
        const currentCacheSlice = readProducts(
            queryDocument,
            client,
            urlSlug,
            sort,
            filter,
            offsetEndCursor,
            pageSize,
        ).products;

        if (currentCacheSlice) {
            if (cachedPartOfProducts) {
                cachedPartOfProducts = mergeItemEdges(
                    cachedPartOfProducts,
                    currentCacheSlice,
                ) as TypeListedProductConnectionFragment['edges'];
            } else {
                cachedPartOfProducts = currentCacheSlice;
            }
        } else {
            return undefined;
        }

        iterationsCounter--;
    }

    return cachedPartOfProducts;
};
