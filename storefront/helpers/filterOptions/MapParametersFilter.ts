import { FilterOptionsStateType } from 'types/productFilter';
import { ProductFilterApi } from 'graphql/generated';

export const mapParametersFilter = (parametersFilter: FilterOptionsStateType): ProductFilterApi => {
    return {
        ...parametersFilter,
        minimalPrice: parametersFilter.minimalPrice?.toString(),
        maximalPrice: parametersFilter.maximalPrice?.toString(),
    };
};
