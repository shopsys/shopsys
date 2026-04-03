import { TypeProductOrderingModeEnum } from 'graphql/types';
import { getStringFromUrlQuery } from './getStringFromUrlQuery';

export const getProductListSortFromUrlQuery = (
    sortQuery: string | string[] | undefined,
): TypeProductOrderingModeEnum | null => {
    const sortQueryAsString = getStringFromUrlQuery(sortQuery);

    return Object.values(TypeProductOrderingModeEnum).some((sort) => sort === sortQueryAsString)
        ? (sortQueryAsString as TypeProductOrderingModeEnum)
        : null;
};
