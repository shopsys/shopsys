import {
    useActualUrlQueryWithoutDefaultPriceFilter,
    useFilterState,
    useIsProductFilterEmpty,
    useIsProductFilterSameAsDefault,
} from '../FilterContext/useFilterState';
import { FilterGroup } from '../FilterGroup/FilterGroup';
import { FilterGroupInStock } from '../FilterGroupInStock/FilterGroupInStock';
import { FilterGroupParameters } from '../FilterGroupParameters/FilterGroupParameters';
import { FilterGroupPrice } from '../FilterGroupPrice/FilterGroupPrice';
import { getIndexOfParameter } from '../helpers/getIndexOfParameter';
import { SelectedParameters } from '../SelectedParameters/SelectedParameters';
import { Icon } from 'components/Basic/Icon/Icon';
import { usePaginationContext } from 'components/Blocks/Pagination/usePaginationContext';
import { Button } from 'components/Forms/Button/Button';
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
import { memo, useCallback, useEffect, useMemo } from 'react';
import { ParametersType } from 'types/productFilter';

type FilterPanelProps = {
    defaultOrderingMode?: ProductOrderingModeEnumApi | null;
    orderingMode: ProductOrderingModeEnumApi | null;
    originalSlug: string | null;
    slug: string;
    panelCloseHandler?: () => void;
    totalCount: number;
};

const TEST_IDENTIFIER = 'blocks-product-filter';
const DEFAULT_NUMBER_OF_SHOWN_FLAGS = 5;
const DEFAULT_NUMBER_OF_SHOWN_BRANDS = 5;
const DEFAULT_NUMBER_OF_SHOWN_PARAMETERS = 5;

export const FilterPanel = memo<FilterPanelProps>(
    ({ defaultOrderingMode, orderingMode, originalSlug, slug, panelCloseHandler, totalCount }) => {
        const t = useTypedTranslationFunction();
        const router = useRouter();
        const [
            {
                selected: { parameters: parametersValue },
                options: productFilterOptions,
            },
        ] = useFilterState();
        const isProductFilterSameAsDefault = useIsProductFilterSameAsDefault();
        const isProductFilterEmpty = useIsProductFilterEmpty();
        const actualUrlQueryWithoutDefaultPriceFilter = useActualUrlQueryWithoutDefaultPriceFilter();
        const [, paginationDispatch] = usePaginationContext();
        const [state] = useFilterState();

        const sortingFromQuery = useMemo(
            () => getProductListSort(parseProductListSortFromQuery(router.query[SORT_QUERY_PARAMETER_NAME])),
            [router.query],
        );

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
            actualUrlQueryWithoutDefaultPriceFilter,
            defaultOrderingMode,
            isProductFilterEmpty,
            isProductFilterSameAsDefault,
            orderingMode,
            originalSlug,
            slug,
            paginationDispatch,
        ]);

        const getIsNotFilteredByParameter = useCallback(
            (parameterUuid: string, data: ParametersType | undefined) => {
                const parameter =
                    parametersValue.length > 0
                        ? parametersValue[getIndexOfParameter(productFilterOptions.parameters ?? [], parameterUuid)]
                        : null;

                const parameterWasNotFound = !parameter;
                const parameterHasNoCheckedValues = !parameter?.values.some((value) => value.checked);

                const optionsParameterMinimalValue = parameter?.minimalValue ?? null;
                const currentParameterMinimalValue =
                    data !== undefined && 'minimalValue' in data ? data.minimalValue : null;
                const areParametersMinimalValuesIdentical =
                    optionsParameterMinimalValue === currentParameterMinimalValue;

                const optionsParameterMaximalValue = parameter?.maximalValue ?? null;
                const currentParameterMaximalValue =
                    data !== undefined && 'maximalValue' in data ? data.maximalValue : null;
                const areParametersMaximalValuesIdentical =
                    optionsParameterMaximalValue === currentParameterMaximalValue;

                return (
                    (parameterWasNotFound || parameterHasNoCheckedValues) &&
                    areParametersMinimalValuesIdentical &&
                    areParametersMaximalValuesIdentical
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

                return (
                    parameter?.values.slice(DEFAULT_NUMBER_OF_SHOWN_PARAMETERS).some((value) => value.checked) === true
                );
            },
            [productFilterOptions.parameters, parametersValue],
        );

        const getAreByDefaultAllFlagsShown = () =>
            state.selected.flags.slice(DEFAULT_NUMBER_OF_SHOWN_FLAGS).some((flag) => flag.checked);

        const getAreByDefaultAllBrandsShown = () =>
            state.selected.brands.slice(DEFAULT_NUMBER_OF_SHOWN_BRANDS).some((brand) => brand.checked);

        return (
            <div
                className="z-aboveOverlay flex h-full flex-col bg-blueLight pb-1 vl:z-above vl:h-auto vl:rounded"
                data-testid={TEST_IDENTIFIER}
            >
                <div className="flex items-center justify-between bg-blueLight py-5 text-2xl vl:hidden">
                    {t('Product filter')}
                    <span
                        className="relative inline-flex h-7 w-7 cursor-pointer items-center justify-center rounded-full border border-primary text-primary hover:bg-black hover:text-white"
                        onClick={panelCloseHandler}
                    >
                        <Icon iconType="icon" icon="Remove" width={24} height={24} className="text-primary" />
                    </span>
                </div>
                <SelectedParameters />
                <div className="h-full overflow-y-scroll px-5 vl:static vl:overflow-visible">
                    <FilterGroupPrice title={t('Price')} isOpen />
                    <FilterGroupInStock title={t('Availability')} inStockCount={productFilterOptions.inStock} isOpen />
                    {productFilterOptions.flags !== null && productFilterOptions.flags.length > 0 && (
                        <FilterGroup
                            title={t('Flags')}
                            filterField="flags"
                            isOpen
                            defaultNumberOfShownFlagsOrBrands={DEFAULT_NUMBER_OF_SHOWN_FLAGS}
                            areByDefaultAllFlagsOrBrandsShown={getAreByDefaultAllFlagsShown()}
                        />
                    )}
                    {productFilterOptions.brands !== null && productFilterOptions.brands.length > 0 && (
                        <FilterGroup
                            title={t('Brands')}
                            filterField="brands"
                            isOpen
                            defaultNumberOfShownFlagsOrBrands={DEFAULT_NUMBER_OF_SHOWN_BRANDS}
                            areByDefaultAllFlagsOrBrandsShown={getAreByDefaultAllBrandsShown()}
                        />
                    )}
                    {productFilterOptions.parameters !== null &&
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
                </div>
                <div className="flex items-center justify-end bg-white py-5 vl:hidden">
                    <Button
                        size="small"
                        onClick={panelCloseHandler}
                        className="inline-block lowercase first-letter:uppercase"
                    >
                        {t('Show')}
                        {` ${totalCount} `}
                        {t('Products count', {
                            count: totalCount,
                        })}
                    </Button>
                </div>
            </div>
        );
    },
);

FilterPanel.displayName = 'FilterPanel';
