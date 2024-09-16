import { TypeProductFilter } from 'graphql/types';
import { FilterOptionsUrlQueryType } from 'types/productFilter';

export const mapParametersFilter = (parametersFilter: FilterOptionsUrlQueryType | null): TypeProductFilter | null => {
    if (!parametersFilter || Object.keys(parametersFilter).length === 0) {
        return null;
    }

    // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
    if (parametersFilter.parameters?.some((param) => param.parameter === undefined)) {
        return null;
    }

    const parameters = parametersFilter.parameters?.map((parameterOption) => ({
        parameter: parameterOption.parameter,
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
