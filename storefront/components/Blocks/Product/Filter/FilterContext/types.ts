import { ProductFilterOptionsFragmentApi } from 'graphql/generated';
import {
    FilterFormBrandType,
    FilterFormFlagType,
    FilterFormParameterType,
    FilterFormParameterValuesType,
    FilterFormType,
} from 'types/productFilter';

export type FilterState = {
    options: ProductFilterOptionsFragmentApi;
    selected: FilterFormType;
};

export type FilterCallbacks = {
    setMinimalPrice: number;
    setMaximalPrice: number;
    setOnlyInStock: boolean;
    setBrands: { value: FilterFormBrandType; index: number };
    setFlags: { value: FilterFormFlagType; index: number };
    setParameter: { value: FilterFormParameterValuesType; parameterIndex: number; valueIndex: number };
    setSliderParameter: {
        value: number | null;
        type: keyof Pick<FilterFormParameterType, 'minimalValue' | 'maximalValue'>;
        index: number;
    };
    uncheckBrand: string;
    uncheckFlag: string;
    uncheckParameter: { uuid: string; valueUuid: string };
    uncheckSliderParameter: string;
    resetPrices: never;
    resetAllParameters: never;
    setFilterOptions: ProductFilterOptionsFragmentApi;
};

export type FilterActions<Actions = FilterCallbacks> = {
    [K in keyof Actions]: Actions[K] extends never
        ? {
              type: K;
          }
        : {
              type: K;
              payload: Actions[K];
          };
}[keyof Actions];
