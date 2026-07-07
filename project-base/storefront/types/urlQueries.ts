import { TypeProductOrderingModeEnum } from 'graphql/types';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    ORDER_CREATED_AFTER_QUERY_PARAMETER_NAME,
    ORDER_CREATED_BEFORE_QUERY_PARAMETER_NAME,
    ORDER_STATUS_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'utils/queryParamNames';
import { FilterOptionsUrlQueryType } from './productFilter';

export type FilterQueries = FilterOptionsUrlQueryType | undefined;

export type UrlQueries = {
    [FILTER_QUERY_PARAMETER_NAME]?: string;
    [SEARCH_QUERY_PARAMETER_NAME]?: string;
    [ORDER_CREATED_AFTER_QUERY_PARAMETER_NAME]?: string;
    [ORDER_CREATED_BEFORE_QUERY_PARAMETER_NAME]?: string;
    [ORDER_STATUS_QUERY_PARAMETER_NAME]?: string;
    [SORT_QUERY_PARAMETER_NAME]?: TypeProductOrderingModeEnum;
    [PAGE_QUERY_PARAMETER_NAME]?: string;
    [LOAD_MORE_QUERY_PARAMETER_NAME]?: string;
};
