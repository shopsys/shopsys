import { enabledSortTypes, initialState } from 'redux/slices/user';
import { ProductOrderingModeEnumApi } from 'graphql/generated';

export const getProductListSort = (sortQuery: string | undefined): ProductOrderingModeEnumApi => {
    return enabledSortTypes.some((sort) => sort === sortQuery)
        ? (sortQuery as ProductOrderingModeEnumApi)
        : initialState.sort;
};
