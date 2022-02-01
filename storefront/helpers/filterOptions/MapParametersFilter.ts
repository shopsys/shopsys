import { FilterOptionsStateType } from 'types/productFilter';
import { ProductFilterApi } from 'graphql/generated';

export const mapParametersFilter = (parametersFilter: FilterOptionsStateType): ProductFilterApi => {
    return {
        ...parametersFilter,
        minimalPrice: parametersFilter.minimalPrice !== null ? parametersFilter.minimalPrice.toString() : null,
        maximalPrice: parametersFilter.maximalPrice !== null ? parametersFilter.maximalPrice.toString() : null,
    };
};
