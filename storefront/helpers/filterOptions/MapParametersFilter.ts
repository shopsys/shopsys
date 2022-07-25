import { ProductFilterApi } from 'graphql/generated';
import { FilterOptionsUrlQueryType } from 'types/productFilter';

export const mapParametersFilter = (parametersFilter: FilterOptionsUrlQueryType | null): ProductFilterApi | null => {
    if (parametersFilter === null) {
        return null;
    }

    return {
        brands: parametersFilter.brands ?? null,
        flags: parametersFilter.flags ?? null,
        onlyInStock: parametersFilter.onlyInStock ?? null,
        parameters: parametersFilter.parameters ?? null,
        minimalPrice: parametersFilter.minimalPrice !== undefined ? parametersFilter.minimalPrice.toString() : null,
        maximalPrice: parametersFilter.maximalPrice !== undefined ? parametersFilter.maximalPrice.toString() : null,
    };
};
