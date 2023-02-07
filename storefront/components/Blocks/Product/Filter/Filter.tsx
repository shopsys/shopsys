import { FilterStyled } from './Filter.style';
import {
    useActualUrlQueryWithoutDefaultPriceFilter,
    useFilterState,
    useIsProductFilterEmpty,
    useIsProductFilterSameAsDefault,
} from './FilterContext/useFilterState';
import { FilterGroup } from './FilterGroup/FilterGroup';
import { FilterGroupInStock } from './FilterGroupInStock/FilterGroupInStock';
import { FilterGroupParameters } from './FilterGroupParameters/FilterGroupParameters';
import { FilterGroupPrice } from './FilterGroupPrice/FilterGroupPrice';
import { getIndexOfParameter } from './helpers/getIndexOfParameter';
import { SelectedParameters } from './SelectedParameters/SelectedParameters';
import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { getQueryWithoutAllParameter } from 'helpers/filterOptions/getQueryWithoutAllParameter';
import { shallowReplaceIfDifferent } from 'helpers/filterOptions/shallowReplaceIfDifferent';
import {
    FILTER_QUERY_PARAMETER_NAME,
    PAGE_QUERY_PARAMETER_NAME,
    SORT_QUERY_PARAMETER_NAME,
} from 'helpers/queryParams/queryParamNames';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useCallback, useEffect, useMemo } from 'react';
import { ParametersType } from 'types/productFilter';

type FilterProps = {
    slug: string;
    originalSlug: string | null;
    orderingMode: ProductOrderingModeEnumApi | null;
    defaultOrderingMode?: ProductOrderingModeEnumApi | null;
};

const TEST_IDENTIFIER = 'blocks-product-filter';
const DEFAULT_NUMBER_OF_SHOWN_FLAGS = 5;
const DEFAULT_NUMBER_OF_SHOWN_BRANDS = 5;
const DEFAULT_NUMBER_OF_SHOWN_PARAMETERS = 5;

export const Filter: FC<FilterProps> = ({ slug, originalSlug, orderingMode, defaultOrderingMode }) => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const sortingFromQuery = getProductListSort(parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME]));
    const [state] = useFilterState();
    const parametersValue = useMemo(() => state.selected.parameters, [state.selected.parameters]);
    const productFilterOptions = useMemo(() => state.options, [state.options]);
    const isProductFilterSameAsDefault = useIsProductFilterSameAsDefault();
    const isProductFilterEmpty = useIsProductFilterEmpty();
    const actualUrlQueryWithoutDefaultPriceFilter = useActualUrlQueryWithoutDefaultPriceFilter();

    const getIsNotFilteredByParameter = useCallback(
        (parameterUuid: string, data: ParametersType | undefined) => {
            const parameter =
                parametersValue.length > 0
                    ? parametersValue[getIndexOfParameter(productFilterOptions.parameters ?? [], parameterUuid)]
                    : null;

            return (
                (!parameter || parameter.values.filter((value) => value.checked).length === 0) &&
                (parameter?.minimalValue ?? null) ===
                    (data !== undefined && 'minimalValue' in data ? data.minimalValue : null) &&
                (parameter?.maximalValue ?? null) ===
                    (data !== undefined && 'maximalValue' in data ? data.maximalValue : null)
            );
        },
        [productFilterOptions.parameters, parametersValue],
    );

    const isFilteredByDefaultlyHiddenParameterValue = useCallback(
        (parameterUuid: string) => {
            const parameter =
                parametersValue.length > 0
                    ? parametersValue[getIndexOfParameter(productFilterOptions.parameters ?? [], parameterUuid)]
                    : null;

            return parameter?.values.slice(DEFAULT_NUMBER_OF_SHOWN_PARAMETERS).some((value) => value.checked) === true;
        },
        [productFilterOptions.parameters, parametersValue],
    );

    const getAreByDefaultAllFlagsShown = () =>
        state.selected.flags.slice(DEFAULT_NUMBER_OF_SHOWN_FLAGS).some((flag) => flag.checked);

    const getAreByDefaultAllBrandsShown = () =>
        state.selected.brands.slice(DEFAULT_NUMBER_OF_SHOWN_BRANDS).some((brand) => brand.checked);

    useEffect(() => {
        const routerQueryWithoutAllParameter = getQueryWithoutAllParameter(router);
        let pathname = slug;

        if (orderingMode === defaultOrderingMode) {
            delete routerQueryWithoutAllParameter[SORT_QUERY_PARAMETER_NAME];
        }

        if (isProductFilterSameAsDefault) {
            delete routerQueryWithoutAllParameter[FILTER_QUERY_PARAMETER_NAME];

            shallowReplaceIfDifferent(router, { pathname, query: routerQueryWithoutAllParameter });
            return;
        }

        pathname = originalSlug ?? slug;

        if (isProductFilterEmpty) {
            delete routerQueryWithoutAllParameter[FILTER_QUERY_PARAMETER_NAME];
        } else {
            delete routerQueryWithoutAllParameter[PAGE_QUERY_PARAMETER_NAME];
            routerQueryWithoutAllParameter[FILTER_QUERY_PARAMETER_NAME] = actualUrlQueryWithoutDefaultPriceFilter;
        }

        if (sortingFromQuery === null && originalSlug !== null && orderingMode !== null) {
            routerQueryWithoutAllParameter[SORT_QUERY_PARAMETER_NAME] = orderingMode;
        }

        shallowReplaceIfDifferent(router, { pathname, query: routerQueryWithoutAllParameter });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [
        originalSlug,
        slug,
        orderingMode,
        defaultOrderingMode,
        actualUrlQueryWithoutDefaultPriceFilter,
        isProductFilterEmpty,
        isProductFilterSameAsDefault,
    ]);

    return (
        <>
            <SelectedParameters />
            <FilterStyled data-testid={TEST_IDENTIFIER}>
                <FilterGroupPrice title={t('Price')} isOpen />
                <FilterGroupInStock title={t('Availability')} inStockCount={productFilterOptions.inStock} isOpen />
                {productFilterOptions.flags.length > 0 && (
                    <FilterGroup
                        title={t('Flags')}
                        filterField="flags"
                        isOpen
                        defaultNumberOfShownFlagsOrBrands={DEFAULT_NUMBER_OF_SHOWN_FLAGS}
                        areByDefaultAllFlagsOrBrandsShown={getAreByDefaultAllFlagsShown()}
                    />
                )}
                {productFilterOptions.brands.length > 0 && (
                    <FilterGroup
                        title={t('Brands')}
                        filterField="brands"
                        isOpen
                        defaultNumberOfShownFlagsOrBrands={DEFAULT_NUMBER_OF_SHOWN_BRANDS}
                        areByDefaultAllFlagsOrBrandsShown={getAreByDefaultAllBrandsShown()}
                    />
                )}
                {productFilterOptions.parameters !== undefined &&
                    productFilterOptions.parameters.map((parametersItem, index) => {
                        const isNotFilteredByParameter = getIsNotFilteredByParameter(
                            parametersItem.uuid,
                            productFilterOptions.parameters?.[index],
                        );

                        return (
                            <FilterGroupParameters
                                key={parametersItem.uuid}
                                parameterParentIndex={index}
                                title={parametersItem.name}
                                data={productFilterOptions.parameters?.[index]}
                                isDefaultCollapsed={parametersItem.isCollapsed && isNotFilteredByParameter}
                                defaultNumberOfShownParameters={DEFAULT_NUMBER_OF_SHOWN_PARAMETERS}
                                areByDefaultAllParametersShown={
                                    !isNotFilteredByParameter &&
                                    isFilteredByDefaultlyHiddenParameterValue(parametersItem.uuid)
                                }
                            />
                        );
                    })}
            </FilterStyled>
        </>
    );
};
