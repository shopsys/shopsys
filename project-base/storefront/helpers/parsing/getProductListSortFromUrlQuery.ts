import { getStringFromUrlQuery } from './getStringFromUrlQuery';
import { ProductOrderingModeEnum } from 'graphql/types';

export const getProductListSortFromUrlQuery = (
    sortQuery: string | string[] | undefined,
): ProductOrderingModeEnum | null => {
    const sortQueryAsString = getStringFromUrlQuery(sortQuery);

    return Object.values(ProductOrderingModeEnum).some((sort) => sort === sortQueryAsString)
        ? (sortQueryAsString as ProductOrderingModeEnum)
        : null;
};
