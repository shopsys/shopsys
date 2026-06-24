import { TypeOrderFilterInput } from 'graphql/types';
import type { ParsedUrlQuery } from 'querystring';
import { getCodeFromUrlQuery } from 'utils/parsing/getCodeFromUrlQuery';
import { getDateTimeFromDateQuery, isDateQueryValid } from 'utils/parsing/getDateTimeFromDateQuery';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import {
    ORDER_CREATED_AFTER_QUERY_PARAMETER_NAME,
    ORDER_CREATED_BEFORE_QUERY_PARAMETER_NAME,
    ORDER_STATUS_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';

export const getOrdersFilterFromUrlQuery = (query: ParsedUrlQuery, timezone: string): TypeOrderFilterInput | null => {
    const search = getStringFromUrlQuery(query[SEARCH_QUERY_PARAMETER_NAME]);
    const createdAfter = getDateTimeFromDateQuery(query[ORDER_CREATED_AFTER_QUERY_PARAMETER_NAME], false, timezone);
    const createdBefore = getDateTimeFromDateQuery(query[ORDER_CREATED_BEFORE_QUERY_PARAMETER_NAME], true, timezone);
    const statusCode = getOrderStatusCodeFromUrlQuery(query[ORDER_STATUS_QUERY_PARAMETER_NAME]);

    if (!search && !createdAfter && !createdBefore && !statusCode) {
        return null;
    }

    return {
        createdAfter,
        createdBefore,
        orderItemsCatnum: null,
        orderItemsProductUuid: null,
        search: search || null,
        statusCodes: statusCode ? [statusCode] : null,
    };
};

export const getOrdersStatuslessFilterFromUrlQuery = (
    query: ParsedUrlQuery,
    timezone: string,
): TypeOrderFilterInput | null => {
    const filter = getOrdersFilterFromUrlQuery(query, timezone);

    if (filter === null) {
        return null;
    }

    return {
        ...filter,
        statusCodes: null,
    };
};

export const hasActiveOrderListFiltersFromUrlQuery = (query: ParsedUrlQuery): boolean => {
    const search = getStringFromUrlQuery(query[SEARCH_QUERY_PARAMETER_NAME]);
    const createdAfter = getStringFromUrlQuery(query[ORDER_CREATED_AFTER_QUERY_PARAMETER_NAME]);
    const createdBefore = getStringFromUrlQuery(query[ORDER_CREATED_BEFORE_QUERY_PARAMETER_NAME]);
    const statusCode = getOrderStatusCodeFromUrlQuery(query[ORDER_STATUS_QUERY_PARAMETER_NAME]);

    return search !== '' || isDateQueryValid(createdAfter) || isDateQueryValid(createdBefore) || statusCode !== null;
};

export const getOrderStatusCodeFromUrlQuery = (statusQuery: string | string[] | undefined): string | null =>
    getCodeFromUrlQuery(statusQuery);
