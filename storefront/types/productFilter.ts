import { SimpleFlagType } from 'types/flag';

export enum FilterOptionsParameterTypeEnum {
    Checkbox = 'checkbox',
    ColorPicker = 'colorPicker',
}

export type ParametersValuesType = {
    uuid: string;
    text: string;
    count: number;
    rgbHex?: string;
};

export type ParametersType = {
    name: string;
    type: FilterOptionsParameterTypeEnum;
    uuid: string;
    values: ParametersValuesType[];
};

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
    rgbHex?: string;
};

export type FilterFormParameterType = {
    parameterName: string;
    parameterUuid: string;
    type: FilterOptionsParameterTypeEnum;
    values: FilterFormParameterValuesType[];
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
};

export type FilterOptionsStateType = {
    brands: string[];
    flags: string[];
    minimalPrice: number | null;
    maximalPrice: number | null;
    onlyInStock: boolean;
    parameters: FilterOptionsParameterStateType[];
};
