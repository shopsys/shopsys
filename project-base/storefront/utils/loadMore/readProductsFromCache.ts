import { DocumentNode } from 'graphql';
import { TypeListedProductConnectionFragment } from 'graphql/requests/products/fragments/ListedProductConnectionFragment.generated';
import { TypeBrandProductsQuery } from 'graphql/requests/products/queries/BrandProductsQuery.generated';
import { TypeCategoryProductsQuery } from 'graphql/requests/products/queries/CategoryProductsQuery.generated';
import { TypeFlagProductsQuery } from 'graphql/requests/products/queries/FlagProductsQuery.generated';
import { TypeProductOrderingModeEnum, Maybe, TypeProductFilter } from 'graphql/types';
import { Client } from 'urql';

export const readProductsFromCache = (
    queryDocument: DocumentNode,
    client: Client,
    urlSlug: string,
    orderingMode: TypeProductOrderingModeEnum | null,
    filter: Maybe<TypeProductFilter>,
    endCursor: string,
    pageSize: number,
): {
    products: TypeListedProductConnectionFragment['edges'] | undefined;
    hasNextPage: boolean;
} => {
    const dataFromCache = client.readQuery<TypeCategoryProductsQuery | TypeBrandProductsQuery | TypeFlagProductsQuery>(
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
