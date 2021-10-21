export type ParameterItemsType = {
    uuid: string;
    text: string;
    count: number;
    rgbHex: string;
};

export type ItemsType = {
    count: number;
    item: {
        uuid: string;
        name: string;
    };
};

export type FilterOptionsApiData = {
    minimalPrice: string;
    maximalPrice: string;
    brands: ItemsType[];
    inStock: number;
    flags: ItemsType[];
    parameters: {
        name: string;
        type: string;
        uuid: string;
        items: ParameterItemsType[];
    }[];
};

export type FilterOptionsType = {
    minimalPrice: number;
    maximalPrice: number;
    brands: ItemsType[];
    inStock: number;
    flags: ItemsType[];
    parameters: {
        name: string;
        type: string;
        uuid: string;
        items: ParameterItemsType[];
    }[];
};

export type FilterFormParametersType = {
    parameter: string;
    values: string[];
};

export type FilterFormType = {
    brands: string[];
    flags: string[];
    maximalPrice: number;
    minimalPrice: number;
    onlyInStock: boolean;
    parameters: FilterFormParametersType[];
};

export type ParametersFilterStateType = {
    brands: string[];
    flags: string[];
    minimalPrice: number | null;
    maximalPrice: number | null;
    onlyInStock: boolean;
    parameters: FilterFormParametersType[];
};
