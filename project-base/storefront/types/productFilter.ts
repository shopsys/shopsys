import {
    TypeParameterCheckboxFilterOption,
    TypeParameterColorFilterOption,
    TypeParameterSliderFilterOption,
} from 'graphql/types';

export type ParametersType =
    | TypeParameterCheckboxFilterOption
    | TypeParameterColorFilterOption
    | TypeParameterSliderFilterOption;

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
