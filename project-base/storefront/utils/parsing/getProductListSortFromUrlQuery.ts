import { getStringFromUrlQuery } from './getStringFromUrlQuery';
import { TypeProductOrderingModeEnum } from 'graphql/types';

export const getProductListSortFromUrlQuery = (
    sortQuery: string | string[] | undefined,
): TypeProductOrderingModeEnum | null => {
    const sortQueryAsString = getStringFromUrlQuery(sortQuery);

    return Object.values(TypeProductOrderingModeEnum).some((sort) => sort === sortQueryAsString)
        ? (sortQueryAsString as TypeProductOrderingModeEnum)
        : null;
};
