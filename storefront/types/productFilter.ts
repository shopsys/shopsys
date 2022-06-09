import { SimpleFlagType } from 'types/flag';

export type ParametersCheckboxValuesType = {
    uuid: string;
    text: string;
    count: number;
};

export type ParametersColorValuesType = {
    uuid: string;
    text: string;
    count: number;
    rgbHex: string | null;
};

export type ParametersCheckboxType = {
    name: string;
    uuid: string;
    values: ParametersCheckboxValuesType[];
    __typename: 'ParameterCheckboxFilterOption';
};

export type ParametersColorType = {
    name: string;
    uuid: string;
    values: ParametersColorValuesType[];
    __typename: 'ParameterColorFilterOption';
};

export type ParametersSliderType = {
    name: string;
    uuid: string;
    minimalValue: number;
    maximalValue: number;
    unit: { name: string } | null;
    __typename: 'ParameterSliderFilterOption';
};

export type ParametersType = ParametersCheckboxType | ParametersColorType | ParametersSliderType;

export type FilterOptionFlagsType = {
    count: number;
    flag: SimpleFlagType;
};

export type BrandsType = {
    count: number;
    brand: {
        uuid: string;
        name: string;
    };
};

export type FilterOptionsType = {
    minimalPrice: number;
    maximalPrice: number;
    inStock: number;
    brands: BrandsType[];
    flags: FilterOptionFlagsType[];
    parameters?: ParametersType[];
    currencyCode: string;
};

export type FilterFormParameterValuesType = {
    checked: boolean;
    uuid: string;
    text: string;
    rgbHex?: string | null;
};

export type FilterFormParameterType = {
    parameterName: string;
    parameterUuid: string;
    values: FilterFormParameterValuesType[];
    minimalValue?: number;
    maximalValue?: number;
    unit?: { name: string } | null;
};

export type FilterFormBrandType = {
    checked: boolean;
    uuid: string;
    name: string;
};

export type FilterFormFlagType = {
    checked: boolean;
    uuid: string;
    name: string;
};

export type FilterFormType = {
    brands: FilterFormBrandType[];
    flags: FilterFormFlagType[];
    maximalPrice: number;
    minimalPrice: number;
    onlyInStock: boolean;
    parameters: FilterFormParameterType[];
};

export type FilterOptionsParameterStateType = {
    parameter: string;
    values: string[];
    minimalValue: number | null;
    maximalValue: number | null;
};

export type FilterOptionsStateType = {
    brands: string[];
    flags: string[];
    minimalPrice: number | null;
    maximalPrice: number | null;
    onlyInStock: boolean;
    parameters: FilterOptionsParameterStateType[];
};
