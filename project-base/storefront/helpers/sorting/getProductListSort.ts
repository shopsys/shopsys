import { ProductOrderingModeEnumApi } from 'graphql/generated';

const enabledSortTypes = [
    ProductOrderingModeEnumApi.PriorityApi,
    ProductOrderingModeEnumApi.PriceAscApi,
    ProductOrderingModeEnumApi.PriceDescApi,
];

export const getProductListSort = (sortQuery: string | undefined): ProductOrderingModeEnumApi | null => {
    return enabledSortTypes.some((sort) => sort === sortQuery)
        ? (sortQuery as ProductOrderingModeEnumApi | null)
        : null;
};
