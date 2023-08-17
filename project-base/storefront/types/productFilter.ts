import {
    ParameterCheckboxFilterOptionApi,
    ParameterColorFilterOptionApi,
    ParameterSliderFilterOptionApi,
} from 'graphql/requests/types';

export type ParametersType =
    | ParameterCheckboxFilterOptionApi
    | ParameterColorFilterOptionApi
    | ParameterSliderFilterOptionApi;

export type FilterOptionsParameterUrlQueryType = {
    parameter: string;
    values?: string[];
    minimalValue?: number;
    maximalValue?: number;
};

export type FilterOptionsUrlQueryType = {
    brands?: string[];
    flags?: string[];
    minimalPrice?: number;
    maximalPrice?: number;
    onlyInStock?: boolean;
    parameters?: FilterOptionsParameterUrlQueryType[];
};
