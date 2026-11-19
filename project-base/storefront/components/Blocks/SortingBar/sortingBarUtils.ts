import { TypeProductOrderingModeEnum } from 'graphql/types';
import { DefaultProductFiltersMapType } from 'store/slices/createSeoCategorySlice';
import { twJoin } from 'tailwind-merge';
import { FilterOptionsUrlQueryType } from 'types/productFilter';

/**
 * A fixed limit instead of measuring the available width, so the bar does not shift after hydration.
 */
const MAX_VISIBLE_SORT_TABS = 4;

/**
 * Desktop tabs shared by the product list sorting and the review sorting, so they look the same:
 * the active option is bold with a 2px indicator under it, hover only darkens the text.
 */
export const getSortingTabClassName = ({ isActive }: { isActive: boolean }) =>
    twJoin(
        'relative inline-flex min-h-11 cursor-pointer items-center rounded-sm px-1 font-secondary text-sm no-underline transition-colors hover:no-underline',
        'after:absolute after:right-1 after:bottom-0 after:left-1 after:h-0.5 after:rounded-full after:transition-colors',
        isActive
            ? 'font-bold text-text-default after:bg-link-default'
            : 'font-semibold text-text-less after:bg-transparent hover:text-text-default',
    );

/**
 * Splits the options into the visible tabs and the rest shown under "More".
 * The selected option always stays visible, it takes the place of the last visible tab.
 */
export const splitSortOptions = (
    sortOptions: TypeProductOrderingModeEnum[],
    selectedSortOption: TypeProductOrderingModeEnum,
) => {
    if (sortOptions.length <= MAX_VISIBLE_SORT_TABS) {
        return { visibleSortOptions: sortOptions, moreSortOptions: [] };
    }

    const visibleSortOptions = sortOptions.slice(0, MAX_VISIBLE_SORT_TABS);
    const isSelectedSortOptionHidden =
        sortOptions.includes(selectedSortOption) && !visibleSortOptions.includes(selectedSortOption);

    if (isSelectedSortOptionHidden) {
        visibleSortOptions[MAX_VISIBLE_SORT_TABS - 1] = selectedSortOption;
    }

    return {
        visibleSortOptions,
        moreSortOptions: sortOptions.filter((sortOption) => !visibleSortOptions.includes(sortOption)),
    };
};

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
