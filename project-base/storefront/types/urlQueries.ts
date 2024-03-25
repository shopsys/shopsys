import { FilterOptionsUrlQueryType } from './productFilter';
import { ProductOrderingModeEnum } from 'graphql/types';
import {
    FILTER_QUERY_PARAMETER_NAME,
    LOAD_MORE_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SEARCH_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParamNames';

export type FilterQueries = FilterOptionsUrlQueryType | undefined;

export type UrlQueries = {
    [FILTER_QUERY_PARAMETER_NAME]?: string;
    [SEARCH_QUERY_PARAMETER_NAME]?: string;
    [SORT_QUERY_PARAMETER_NAME]?: ProductOrderingModeEnum;
    [PAGE_QUERY_PARAMETER_NAME]?: string;
    [LOAD_MORE_QUERY_PARAMETER_NAME]?: string;
};
