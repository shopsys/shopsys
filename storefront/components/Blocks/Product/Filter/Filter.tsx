import { FilterStyled } from './Filter.style';
import { FilterGroup } from './FilterGroup/FilterGroup';
import { FilterGroupInStock } from './FilterGroupInStock/FilterGroupInStock';
import { FilterGroupParameters } from './FilterGroupParameters/FilterGroupParameters';
import { FilterGroupPrice } from './FilterGroupPrice/FilterGroupPrice';
import { getDefaultFormValues } from './formMeta';
import { getIndexOfParameter } from './helpers/getIndexOfParameter';
import { SelectedParameters } from './SelectedParameters/SelectedParameters';
import { Form } from 'components/Forms/Form/Form';
import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { getActualUrlQueryWithoutDefaultPriceFilter } from 'helpers/filterOptions/getActualUrlQueryWithoutDefaultPriceFilter';
import { getFilterOptions } from 'helpers/filterOptions/getFilterOptions';
import { getIsProductFilterEmpty } from 'helpers/filterOptions/getIsProductFilterEmpty';
import { getIsProductFilterSameAsDefault } from 'helpers/filterOptions/getIsProductFilterSameAsDefault';
import { getQueryWithoutAllParameter } from 'helpers/filterOptions/getQueryWithoutAllParameter';
import { mapParametersFilter } from 'helpers/filterOptions/mapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/parseFilterOptionsFromQuery';
import { shallowReplaceIfDifferent } from 'helpers/filterOptions/shallowReplaceIfDifferent';
import { getProductListSort } from 'helpers/sorting/getProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/parseProductListSortFromQuery';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useCallback, useEffect, useMemo } from 'react';
import { FormProvider, useForm, useWatch } from 'react-hook-form';
import { FilterFormParameterType, FilterFormType, FilterOptionsType } from 'types/productFilter';

type FilterProps = {
    productFilterOptions: FilterOptionsType;
    slug: string;
    originalSlug: string | null;
    orderingMode: ProductOrderingModeEnumApi | null;
    defaultOrderingMode?: ProductOrderingModeEnumApi | null;
};

const TEST_IDENTIFIER = 'blocks-product-filter';

export const ProductFilter: FC<FilterProps> = ({
    productFilterOptions,
    slug,
    originalSlug,
    orderingMode,
    defaultOrderingMode,
}) => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const sortingFromQuery = getProductListSort(parseProductListSortFromQuery(router.query.sort));

    const deepComparedProductFilterOptions = useMemo(
        () => productFilterOptions,
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [JSON.stringify(productFilterOptions)],
    );

    const formProviderMethods = useForm<FilterFormType>({
        defaultValues: getDefaultFormValues(
            mapParametersFilter(getFilterOptions(parseFilterOptionsFromQuery(router.query.filter))),
            deepComparedProductFilterOptions,
            originalSlug,
        ),
    });

    const [brandsValue, flagsValue, parametersValue, isOnlyInStock, minimalPrice, maximalPrice] = useWatch({
        name: ['brands', 'flags', 'parameters', 'onlyInStock', 'minimalPrice', 'maximalPrice'],
        control: formProviderMethods.control,
    });

    const getIsNotFilteredByParameter = useCallback(
        (parameterUuid: string) => {
            const parameter =
                parametersValue[getIndexOfParameter(deepComparedProductFilterOptions.parameters ?? [], parameterUuid)];

            return (
                parameter.values.filter((value) => value.checked).length === 0 &&
                parameter.minimalValue === null &&
                parameter.maximalValue === null
            );
        },
        [deepComparedProductFilterOptions.parameters, parametersValue],
    );

    const checkedBrands = useMemo(() => brandsValue.filter((brand) => brand.checked), [brandsValue]);
    const checkedFlags = useMemo(() => flagsValue.filter((brand) => brand.checked), [flagsValue]);
    const checkedParameters = useMemo(() => {
        const newCheckedParameters: FilterFormParameterType[] = [];

        parametersValue.forEach((currentParameterWithFilteredValues) => {
            // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
            const filteredValues = currentParameterWithFilteredValues.values?.filter((value) => value.checked) ?? [];

            if (filteredValues.length > 0) {
                newCheckedParameters.push({ ...currentParameterWithFilteredValues, values: filteredValues });
            } else if (
                'minimalValue' in currentParameterWithFilteredValues &&
                'maximalValue' in currentParameterWithFilteredValues
            ) {
                const parameter = deepComparedProductFilterOptions.parameters?.find(
                    (param) => param.uuid === currentParameterWithFilteredValues.parameterUuid,
                );
                if (
                    (currentParameterWithFilteredValues.minimalValue !== null &&
                        parameter &&
                        'minimalValue' in parameter &&
                        parameter.minimalValue !== currentParameterWithFilteredValues.minimalValue) ||
                    (currentParameterWithFilteredValues.maximalValue !== null &&
                        parameter &&
                        'maximalValue' in parameter &&
                        parameter.maximalValue !== currentParameterWithFilteredValues.maximalValue)
                ) {
                    newCheckedParameters.push({ ...currentParameterWithFilteredValues, values: [] });
                }
            }
        });

        return newCheckedParameters;
    }, [deepComparedProductFilterOptions.parameters, parametersValue]);

    useEffect(() => {
        const routerQueryWithoutAllParameter = getQueryWithoutAllParameter(router);
        let pathname = slug;
        const isProductFilterSameAsDefault = getIsProductFilterSameAsDefault(
            checkedBrands,
            checkedFlags,
            minimalPrice,
            maximalPrice,
            isOnlyInStock,
            checkedParameters,
            deepComparedProductFilterOptions,
        );

        if (orderingMode === defaultOrderingMode) {
            delete routerQueryWithoutAllParameter.sort;
        }

        if (isProductFilterSameAsDefault) {
            delete routerQueryWithoutAllParameter.filter;

            shallowReplaceIfDifferent(router, { pathname, query: routerQueryWithoutAllParameter });
            return;
        }

        const isProductFilterEmpty = getIsProductFilterEmpty(
            checkedBrands,
            checkedFlags,
            minimalPrice,
            maximalPrice,
            isOnlyInStock,
            checkedParameters,
            deepComparedProductFilterOptions,
        );
        pathname = originalSlug ?? slug;

        if (isProductFilterEmpty) {
            delete routerQueryWithoutAllParameter.filter;
        } else {
            delete routerQueryWithoutAllParameter.page;
            routerQueryWithoutAllParameter.filter = getActualUrlQueryWithoutDefaultPriceFilter(
                checkedBrands,
                checkedFlags,
                minimalPrice,
                maximalPrice,
                isOnlyInStock,
                checkedParameters,
                deepComparedProductFilterOptions,
            );
        }

        if (sortingFromQuery === null && originalSlug !== null && orderingMode !== null) {
            routerQueryWithoutAllParameter.sort = orderingMode;
        }

        shallowReplaceIfDifferent(router, { pathname, query: routerQueryWithoutAllParameter });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [
        checkedBrands,
        checkedFlags,
        minimalPrice,
        maximalPrice,
        isOnlyInStock,
        checkedParameters,
        deepComparedProductFilterOptions,
        originalSlug,
        slug,
        orderingMode,
        defaultOrderingMode,
    ]);

    return (
        <FormProvider {...formProviderMethods}>
            <SelectedParameters
                productFilterOptions={deepComparedProductFilterOptions}
                checkedBrands={checkedBrands}
                checkedFlags={checkedFlags}
                checkedParameters={checkedParameters}
                minimalPrice={minimalPrice}
                maximalPrice={maximalPrice}
                isOnlyInStock={isOnlyInStock}
            />
            <FilterStyled data-testid={TEST_IDENTIFIER}>
                <Form>
                    <FilterGroupPrice
                        title={t('Price')}
                        minimalPrice={deepComparedProductFilterOptions.minimalPrice}
                        maximalPrice={deepComparedProductFilterOptions.maximalPrice}
                        isOpen={true}
                    />

                    <FilterGroupInStock
                        title={t('Availability')}
                        inStockCount={deepComparedProductFilterOptions.inStock}
                        isOpen={true}
                    />

                    {deepComparedProductFilterOptions.flags.length > 0 && (
                        <FilterGroup
                            title={t('Flags')}
                            filterField="flags"
                            data={deepComparedProductFilterOptions.flags}
                            isOpen={true}
                        />
                    )}

                    {deepComparedProductFilterOptions.brands.length > 0 && (
                        <FilterGroup
                            title={t('Brands')}
                            filterField="brands"
                            data={deepComparedProductFilterOptions.brands}
                            isOpen={true}
                        />
                    )}

                    {deepComparedProductFilterOptions.parameters !== undefined &&
                        parametersValue.map((parametersItem, index) => (
                            <FilterGroupParameters
                                key={parametersItem.parameterUuid}
                                parameterParentIndex={index}
                                title={parametersItem.parameterName}
                                data={deepComparedProductFilterOptions.parameters?.[index]}
                                isDefaultCollapsed={
                                    parametersItem.isCollapsed &&
                                    getIsNotFilteredByParameter(parametersItem.parameterUuid)
                                }
                            />
                        ))}
                </Form>
            </FilterStyled>
        </FormProvider>
    );
};
