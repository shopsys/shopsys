import { DocumentNode } from 'graphql';
import { ListedProductConnectionFragment } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';
import { BrandProductsQuery } from 'graphql/requests/products/queries/BrandProductsQuery.generated';
import { CategoryProductsQuery } from 'graphql/requests/products/queries/CategoryProductsQuery.generated';
import { FlagProductsQuery } from 'graphql/requests/products/queries/FlagProductsQuery.generated';
import { ProductOrderingModeEnum, Maybe, ProductFilter } from 'graphql/types';
import { Client } from 'urql';

export const readProductsFromCache = (
    queryDocument: DocumentNode,
    client: Client,
    urlSlug: string,
    orderingMode: ProductOrderingModeEnum | null,
    filter: Maybe<ProductFilter>,
    endCursor: string,
    pageSize: number,
): {
    products: ListedProductConnectionFragment['edges'] | undefined;
    hasNextPage: boolean;
} => {
    const dataFromCache = client.readQuery<CategoryProductsQuery | BrandProductsQuery | FlagProductsQuery>(
        queryDocument,
        {
            urlSlug,
            orderingMode,
            filter,
            endCursor,
            pageSize,
        },
    )?.data?.products;

    return {
        products: dataFromCache?.edges,
        hasNextPage: !!dataFromCache?.pageInfo.hasNextPage,
    };
};
