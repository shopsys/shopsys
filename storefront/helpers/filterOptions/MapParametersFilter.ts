import { ProductFilterApi } from 'graphql/generated';
import { FilterOptionsStateType } from 'types/productFilter';

export const mapParametersFilter = (parametersFilter: FilterOptionsStateType): ProductFilterApi => {
    return {
        ...parametersFilter,
        minimalPrice: parametersFilter.minimalPrice !== null ? parametersFilter.minimalPrice.toString() : null,
        maximalPrice: parametersFilter.maximalPrice !== null ? parametersFilter.maximalPrice.toString() : null,
    };
};
