import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { enabledSortTypes, initialState } from 'redux/slices/user';

export const getProductListSort = (sortQuery: string | undefined): ProductOrderingModeEnumApi => {
    return enabledSortTypes.some((sort) => sort === sortQuery)
        ? (sortQuery as ProductOrderingModeEnumApi)
        : initialState.sort;
};
