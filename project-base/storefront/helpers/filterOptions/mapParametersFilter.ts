import { ProductFilterApi } from 'graphql/generated';
import { FilterOptionsUrlQueryType } from 'types/productFilter';

export const mapParametersFilter = (parametersFilter: FilterOptionsUrlQueryType | null): ProductFilterApi | null => {
    if (!parametersFilter || Object.keys(parametersFilter).length === 0) {
        return null;
    }

    const parameters = parametersFilter.parameters?.map((parameterOption) => ({
        ...parameterOption,
        values: parameterOption.values ?? [],
        maximalValue: parameterOption.maximalValue ?? null,
        minimalValue: parameterOption.minimalValue ?? null,
    }));

    return {
        brands: parametersFilter.brands ?? null,
        flags: parametersFilter.flags ?? null,
        onlyInStock: parametersFilter.onlyInStock ?? null,
        parameters: parameters ?? null,
        minimalPrice: parametersFilter.minimalPrice !== undefined ? parametersFilter.minimalPrice.toString() : null,
        maximalPrice: parametersFilter.maximalPrice !== undefined ? parametersFilter.maximalPrice.toString() : null,
    };
};
