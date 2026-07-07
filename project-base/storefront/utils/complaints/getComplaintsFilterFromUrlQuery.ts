import { TypeComplaintFilterInput } from 'graphql/types';
import type { ParsedUrlQuery } from 'querystring';
import { getCodeFromUrlQuery } from 'utils/parsing/getCodeFromUrlQuery';
import { getDateTimeFromDateQuery, isDateQueryValid } from 'utils/parsing/getDateTimeFromDateQuery';
import { getStringFromUrlQuery } from 'utils/parsing/getStringFromUrlQuery';
import {
    COMPLAINT_CREATED_AFTER_QUERY_PARAMETER_NAME,
    COMPLAINT_CREATED_BEFORE_QUERY_PARAMETER_NAME,
    COMPLAINT_STATUS_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';

export const getComplaintsFilterFromUrlQuery = (
    query: ParsedUrlQuery,
    timezone: string,
): TypeComplaintFilterInput | null => {
    const search = getStringFromUrlQuery(query[SEARCH_QUERY_PARAMETER_NAME]);
    const createdAfter = getDateTimeFromDateQuery(query[COMPLAINT_CREATED_AFTER_QUERY_PARAMETER_NAME], false, timezone);
    const createdBefore = getDateTimeFromDateQuery(
        query[COMPLAINT_CREATED_BEFORE_QUERY_PARAMETER_NAME],
        true,
        timezone,
    );
    const statusCode = getComplaintStatusCodeFromUrlQuery(query[COMPLAINT_STATUS_QUERY_PARAMETER_NAME]);

    if (!search && !createdAfter && !createdBefore && statusCode === null) {
        return null;
    }

    return {
        createdAfter,
        createdBefore,
        search: search || null,
        statusCodes: statusCode !== null ? [statusCode] : null,
    };
};

export const getComplaintsStatuslessFilterFromUrlQuery = (
    query: ParsedUrlQuery,
    timezone: string,
): TypeComplaintFilterInput | null => {
    const filter = getComplaintsFilterFromUrlQuery(query, timezone);

    if (filter === null) {
        return null;
    }

    return {
        ...filter,
        statusCodes: null,
    };
};

export const hasActiveComplaintListFiltersFromUrlQuery = (query: ParsedUrlQuery): boolean => {
    const search = getStringFromUrlQuery(query[SEARCH_QUERY_PARAMETER_NAME]);
    const createdAfter = getStringFromUrlQuery(query[COMPLAINT_CREATED_AFTER_QUERY_PARAMETER_NAME]);
    const createdBefore = getStringFromUrlQuery(query[COMPLAINT_CREATED_BEFORE_QUERY_PARAMETER_NAME]);
    const statusCode = getComplaintStatusCodeFromUrlQuery(query[COMPLAINT_STATUS_QUERY_PARAMETER_NAME]);

    return search !== '' || isDateQueryValid(createdAfter) || isDateQueryValid(createdBefore) || statusCode !== null;
};

export const getComplaintStatusCodeFromUrlQuery = (statusQuery: string | string[] | undefined): string | null =>
    getCodeFromUrlQuery(statusQuery);
