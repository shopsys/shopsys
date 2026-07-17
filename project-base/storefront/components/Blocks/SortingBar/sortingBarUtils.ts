import { TypeProductOrderingModeEnum } from 'graphql/types';
import { DefaultProductFiltersMapType } from 'store/slices/createSeoCategorySlice';
import { FilterOptionsUrlQueryType } from 'types/productFilter';

export const getIsPriceRelatedSortOption = (sortOption: TypeProductOrderingModeEnum) =>
    sortOption === TypeProductOrderingModeEnum.PriceAsc || sortOption === TypeProductOrderingModeEnum.PriceDesc;

export const getActiveFilterCount = (
    currentFilter: FilterOptionsUrlQueryType | null,
    defaultProductFiltersMap: DefaultProductFiltersMapType,
) => {
    if (!currentFilter && !getHasDefaultFilters(defaultProductFiltersMap)) {
        return 0;
    }

    const parametersMap = new Map(currentFilter?.parameters?.map((parameter) => [parameter.parameter, parameter]));

    for (const [defaultParameterUuid, defaultParameterSelectedValues] of Array.from(
        defaultProductFiltersMap.parameters,
    )) {
        parametersMap.set(defaultParameterUuid, {
            parameter: defaultParameterUuid,
            values: Array.from(defaultParameterSelectedValues),
        });
    }

    const brandsCount = currentFilter?.brands?.length ?? 0;
    const flagsCount = new Set([...(currentFilter?.flags ?? []), ...Array.from(defaultProductFiltersMap.flags)]).size;
    const inStockCount = currentFilter?.onlyInStock ? 1 : 0;
    const priceCount = currentFilter?.minimalPrice !== undefined || currentFilter?.maximalPrice !== undefined ? 1 : 0;
    const parametersCount = Array.from(parametersMap.values()).reduce((count, parameter) => {
        if (parameter.values?.length) {
            return count + parameter.values.length;
        }

        return parameter.minimalValue !== undefined || parameter.maximalValue !== undefined ? count + 1 : count;
    }, 0);

    return brandsCount + flagsCount + inStockCount + priceCount + parametersCount;
};

const getHasDefaultFilters = (defaultProductFiltersMap: DefaultProductFiltersMapType) =>
    defaultProductFiltersMap.flags.size > 0 || defaultProductFiltersMap.parameters.size > 0;
