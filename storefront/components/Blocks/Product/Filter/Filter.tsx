import { FilterStyled } from './Filter.style';
import { FilterGroup } from './FilterGroup/FilterGroup';
import { FilterGroupInStock } from './FilterGroupInStock/FilterGroupInStock';
import { FilterGroupParameters } from './FilterGroupParameters/FilterGroupParameters';
import { FilterGroupPrice } from './FilterGroupPrice/FilterGroupPrice';
import { getDefaultFormValues } from './formMeta';
import { getIndexOfParameter } from './helpers/getIndexOfParameter';
import { SelectedParameters } from './SelectedParameters/SelectedParameters';
import Form from 'components/Forms/Form';
import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { getActualUrlQueryWithoutDefaultPriceFilter } from 'helpers/filterOptions/GetActualUrlQueryWithoutDefaultPriceFilter';
import { getFilterOptions } from 'helpers/filterOptions/GetFilterOptions';
import { getIsProductFilterEmpty } from 'helpers/filterOptions/GetIsProductFilterEmpty';
import { getIsProductFilterSameAsDefault } from 'helpers/filterOptions/GetIsProductFilterSameAsDefault';
import { getQueryWithoutAllParameter } from 'helpers/filterOptions/GetQueryWithoutAllParameter';
import { mapParametersFilter } from 'helpers/filterOptions/MapParametersFilter';
import { parseFilterOptionsFromQuery } from 'helpers/filterOptions/ParseFilterOptionsFromQuery';
import { shallowReplaceIfDifferent } from 'helpers/filterOptions/ShallowReplaceIfDifferent';
import { getProductListSort } from 'helpers/sorting/GetProductListSort';
import { parseProductListSortFromQuery } from 'helpers/sorting/ParseProductListSortFromQuery';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useCallback, useEffect, useMemo } from 'react';
import { FormProvider, useForm, useWatch } from 'react-hook-form';
import { FilterFormParameterType, FilterFormType, FilterOptionsType } from 'types/productFilter';

type FilterProps = {
    productFilterOptions: FilterOptionsType;
    slug: string;
    originalSlug: string | null;
};

const TEST_IDENTIFIER = 'blocks-product-filter';

export const ProductFilter: FC<FilterProps> = ({ productFilterOptions, slug, originalSlug }) => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const sortingFromQuery = getProductListSort(parseProductListSortFromQuery(router.query.sort));

    const deepComparedProductFitlerOptions = useMemo(
        () => productFilterOptions,
        // eslint-disable-next-line react-hooks/exhaustive-deps
        [JSON.stringify(productFilterOptions)],
    );

    const formProviderMethods = useForm<FilterFormType>({
        defaultValues: getDefaultFormValues(
            mapParametersFilter(getFilterOptions(parseFilterOptionsFromQuery(router.query.filter))),
            deepComparedProductFitlerOptions,
            originalSlug,
        ),
    });

    const [brandsValue, flagsValue, parametersValue, isOnlyInStock, minimalPrice, maximalPrice] = useWatch({
        name: ['brands', 'flags', 'parameters', 'onlyInStock', 'minimalPrice', 'maximalPrice'],
        control: formProviderMethods.control,
    });

    const getIsNotFilteredByParameter = useCallback(
        (parameterUuid: string) => {
            const parameter = parametersValue[getIndexOfParameter(parametersValue, parameterUuid)];

            return (
                parameter.values.filter((value) => value.checked).length === 0 &&
                parameter.minimalValue === null &&
                parameter.maximalValue === null
            );
        },
        [parametersValue],
    );

    const checkedBrands = useMemo(() => brandsValue.filter((brand) => brand.checked), [brandsValue]);
    const checkedFlags = useMemo(() => flagsValue.filter((brand) => brand.checked), [flagsValue]);
    const checkedParameters = useMemo(() => {
        const newCheckedParameters: FilterFormParameterType[] = [];

        parametersValue.forEach((currentParameterWithFilteredValues) => {
            const filteredValues = currentParameterWithFilteredValues.values.filter((value) => value.checked);

            if (filteredValues.length > 0) {
                newCheckedParameters.push({ ...currentParameterWithFilteredValues, values: filteredValues });
            }
        });

        return newCheckedParameters;
    }, [parametersValue]);

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
            deepComparedProductFitlerOptions,
        );

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
            deepComparedProductFitlerOptions,
        );
        pathname = originalSlug ?? slug;
        if (isProductFilterEmpty) {
            delete routerQueryWithoutAllParameter.filter;
        } else {
            routerQueryWithoutAllParameter.filter = getActualUrlQueryWithoutDefaultPriceFilter(
                checkedBrands,
                checkedFlags,
                minimalPrice,
                maximalPrice,
                isOnlyInStock,
                checkedParameters,
                deepComparedProductFitlerOptions,
            );
        }

        if (sortingFromQuery === null) {
            routerQueryWithoutAllParameter.sort = ProductOrderingModeEnumApi.PriorityApi;
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
        deepComparedProductFitlerOptions,
        originalSlug,
        slug,
    ]);

    return (
        <FormProvider {...formProviderMethods}>
            <SelectedParameters
                checkedBrands={checkedBrands}
                checkedFlags={checkedFlags}
                checkedParameters={checkedParameters}
                minimalPrice={minimalPrice}
                maximalPrice={maximalPrice}
                isOnlyInStock={isOnlyInStock}
                productFilterOptions={deepComparedProductFitlerOptions}
            />
            <FilterStyled data-testid={TEST_IDENTIFIER}>
                <Form>
                    <FilterGroupPrice
                        title={t('Price')}
                        minimalPrice={deepComparedProductFitlerOptions.minimalPrice}
                        maximalPrice={deepComparedProductFitlerOptions.maximalPrice}
                        isOpen={true}
                    />

                    <FilterGroupInStock
                        title={t('Availability')}
                        inStockCount={deepComparedProductFitlerOptions.inStock}
                        isOpen={true}
                    />

                    {deepComparedProductFitlerOptions.flags.length > 0 && (
                        <FilterGroup
                            title={t('Flags')}
                            filterField="flags"
                            data={deepComparedProductFitlerOptions.flags}
                            isOpen={true}
                        />
                    )}

                    {deepComparedProductFitlerOptions.brands.length > 0 && (
                        <FilterGroup
                            title={t('Brands')}
                            filterField="brands"
                            data={deepComparedProductFitlerOptions.brands}
                            isOpen={true}
                        />
                    )}

                    {deepComparedProductFitlerOptions.parameters !== undefined &&
                        parametersValue.map((parametersItem, index) => (
                            <FilterGroupParameters
                                key={parametersItem.parameterUuid}
                                parameterParentIndex={index}
                                title={parametersItem.parameterName}
                                data={deepComparedProductFitlerOptions.parameters?.[index]}
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
