import { FilterStyled } from './Filter.style';
import FilterGroup from './FilterGroup';
import FilterGroupInStock from './FilterGroupInStock';
import FilterGroupParameters from './FilterGroupParameters';
import FilterGroupPrice from './FilterGroupPrice';
import { getIndexOfParameter } from './helpers/getIndexOfParameter';
import { getIndexOfParameterValue } from './helpers/getIndexOfParameterValue';
import SelectedParameters from './SelectedParameters';
import Form from 'components/Forms/Form';
import { isProductFilterWithoutChanges } from 'helpers/IsProductFilterWithoutChanges';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useComponentUpdate } from 'hooks/helpers/UseComponentUpdate';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useEffectOnce } from 'hooks/ui/useEffectOnce';
import { useRouter } from 'next/router';
import { FC, useCallback, useEffect, useMemo } from 'react';
import { FormProvider, useFieldArray, useWatch } from 'react-hook-form';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { optionsFilterActions } from 'redux/slices/optionsFilter';
import { FilterFormParameterType, FilterFormType, FilterOptionsType, ParametersType } from 'types/productFilter';

type FilterProps = {
    productFilterOptions: FilterOptionsType;
    slug?: string;
    formUpdateDependency?: boolean;
};

const TEST_IDENTIFIER = 'blocks-product-filter';

const Filter: FC<FilterProps> = ({ productFilterOptions, slug, formUpdateDependency }) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();
    const parametersFilterState = useShopsysSelector((state) => state.optionsFilter);

    const defaultBrandValues = useMemo(
        () =>
            productFilterOptions.brands.map((value) => ({
                ...value.brand,
                checked: false,
            })),
        [productFilterOptions.brands],
    );

    const defaultFlagValues = useMemo(() => {
        return productFilterOptions.flags.map((value) => ({
            ...value.flag,
            checked: value.isSelected,
        }));
    }, [productFilterOptions.flags]);

    const getParametersValues = useCallback((): FilterFormParameterType[] => {
        if (productFilterOptions.parameters === undefined) {
            return [];
        }

        function getValues(parameter: ParametersType) {
            if (!('values' in parameter)) {
                return [];
            }

            return parameter.values.map((value) => ({
                ...value,
                checked: value.isSelected,
            }));
        }

        return productFilterOptions.parameters.map((parameter) => ({
            parameterName: parameter.name,
            parameterUuid: parameter.uuid,
            values: getValues(parameter),
            isCollapsed: parameter.isCollapsed,
            minimalValue: 'minimalValue' in parameter ? parameter.minimalValue : undefined,
            maximalValue: 'maximalValue' in parameter ? parameter.maximalValue : undefined,
            selectedValue: 'selectedValue' in parameter ? parameter.selectedValue : undefined,
            unit: 'unit' in parameter ? parameter.unit : undefined,
        }));
    }, [productFilterOptions.parameters]);

    const formProviderMethods = useShopsysForm<FilterFormType>(undefined, {
        brands: defaultBrandValues,
        flags: defaultFlagValues,
        parameters: getParametersValues(),
        onlyInStock: false,
        minimalPrice: productFilterOptions.minimalPrice,
        maximalPrice: productFilterOptions.maximalPrice,
    });

    const { fields: fieldsParameters } = useFieldArray({ control: formProviderMethods.control, name: 'parameters' });
    const brandsValue = useWatch({ name: 'brands', control: formProviderMethods.control });
    const flagsValue = useWatch({ name: 'flags', control: formProviderMethods.control });
    const parametersValue = useWatch({ name: 'parameters', control: formProviderMethods.control });
    const onlyInStockValue = useWatch({ name: 'onlyInStock', control: formProviderMethods.control });
    const minimalPriceValue = useWatch({ name: 'minimalPrice', control: formProviderMethods.control });
    const maximalPriceValue = useWatch({ name: 'maximalPrice', control: formProviderMethods.control });

    useEffect(() => {
        const parameters = [];

        const defaultCheckedParamsCount = productFilterOptions.parameters?.reduce(
            (partialSum, parameter) =>
                'values' in parameter
                    ? partialSum + parameter.values.filter((value) => value.isSelected).length
                    : partialSum,
            0,
        );

        const checkedParametersCount = parametersValue.reduce(
            (partialSum, parameter) => partialSum + parameter.values.filter((value) => value.checked).length,
            0,
        );

        for (const parameter of parametersValue) {
            const checkedValues = [];

            const param = productFilterOptions.parameters?.find((p) => p.uuid === parameter.parameterUuid);

            if (defaultCheckedParamsCount !== checkedParametersCount) {
                // NOTE: "parameter.values" is sometimes undefined despite typing
                // eslint-disable-next-line @typescript-eslint/no-unnecessary-condition
                for (const value of parameter.values ?? []) {
                    const defaultChecked =
                        param && 'values' in param
                            ? param.values.find((val) => val.uuid === value.uuid)?.isSelected
                            : false;
                    if (value.checked && !defaultChecked) {
                        checkedValues.push(value.uuid);
                    }
                }
            }

            if (
                checkedValues.length === 0 &&
                parameter.minimalValue === undefined &&
                parameter.maximalValue === undefined
            ) {
                continue;
            }

            parameters.push({
                parameter: parameter.parameterUuid,
                values: checkedValues,
                minimalValue: parameter.minimalValue ?? null,
                maximalValue: parameter.maximalValue ?? null,
            });
        }

        dispatch(optionsFilterActions.setParametersFilter(parameters));
    }, [dispatch, parametersValue, productFilterOptions.parameters]);

    useComponentUpdate(() => {
        const brands = brandsValue.reduce(function (result: string[], brand) {
            if (brand.checked === true) {
                result.push(brand.uuid);
            }
            return result;
        }, []);

        dispatch(optionsFilterActions.setBrandsFilter(brands));
    }, [brandsValue]);

    useComponentUpdate(() => {
        const flags = flagsValue.reduce(function (result: string[], flag) {
            if (
                flag.checked === true &&
                !productFilterOptions.flags.find((productFlag) => productFlag.flag.uuid === flag.uuid)?.isSelected
            ) {
                result.push(flag.uuid);
            }
            return result;
        }, []);

        dispatch(optionsFilterActions.setFlagsFilter(flags));
    }, [flagsValue]);

    useComponentUpdate(() => {
        dispatch(optionsFilterActions.setOnlyInStockFilter(onlyInStockValue));
    }, [onlyInStockValue]);

    useComponentUpdate(() => {
        dispatch(optionsFilterActions.setMinimalPriceFilter(minimalPriceValue));
    }, [minimalPriceValue]);

    useComponentUpdate(() => {
        dispatch(optionsFilterActions.setMaximalPriceFilter(maximalPriceValue));
    }, [maximalPriceValue]);

    useComponentUpdate(() => {
        formProviderMethods.setValue(`brands`, defaultBrandValues);
        formProviderMethods.setValue(`flags`, defaultFlagValues);
        formProviderMethods.setValue(`parameters`, getParametersValues());
        formProviderMethods.setValue(`onlyInStock`, false);
        formProviderMethods.setValue(`minimalPrice`, productFilterOptions.minimalPrice);
        formProviderMethods.setValue(`maximalPrice`, productFilterOptions.maximalPrice);
    }, [slug, formUpdateDependency]);

    useEffect(() => {
        const queryParams = router.query;
        const pathname = router.asPath.split('?')[0];

        delete queryParams.all;
        if (isProductFilterWithoutChanges(parametersFilterState, productFilterOptions)) {
            delete queryParams.filter;
        } else {
            queryParams.filter = JSON.stringify(parametersFilterState);
        }

        router.replace({ pathname, query: queryParams }, undefined, {
            scroll: false,
            shallow: true,
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [parametersFilterState]);

    const onBrandCheck = (uuid: string) => {
        const indexOfValue = brandsValue.findIndex((value) => value.uuid === uuid);

        formProviderMethods.setValue(`brands.${indexOfValue}.checked`, true);
    };

    const onFlagCheck = (uuid: string) => {
        const indexOfValue = flagsValue.findIndex((value) => value.uuid === uuid);

        formProviderMethods.setValue(`flags.${indexOfValue}.checked`, true);
    };

    const onParameterCheck = (parameterUuid: string, parameterValueUuid: string) => {
        const indexOfParameter = getIndexOfParameter(parametersValue, parameterUuid);
        const indexOfValue = getIndexOfParameterValue(parametersValue, indexOfParameter, parameterValueUuid);

        formProviderMethods.setValue(`parameters.${indexOfParameter}.values.${indexOfValue}.checked`, true);
    };

    const getIsNotFilteredByParameter = useCallback(
        (parameterUuid: string) => {
            const parameter = parametersValue[getIndexOfParameter(parametersValue, parameterUuid)];

            return (
                parameter.values.filter((value) => value.checked).length === 0 &&
                parameter.minimalValue === undefined &&
                parameter.maximalValue === undefined
            );
        },
        [parametersValue],
    );

    // this useEffect triggered only on the first render
    useEffectOnce(() => {
        parametersFilterState.brands.forEach((brandUuid) => onBrandCheck(brandUuid));
        parametersFilterState.flags.forEach((flagUuid) => onFlagCheck(flagUuid));
        parametersFilterState.parameters.forEach((parameterItem) => {
            parameterItem.values.forEach((parameterValueUuid) =>
                onParameterCheck(parameterItem.parameter, parameterValueUuid),
            );
            if (parameterItem.minimalValue !== null) {
                const indexOfParameter = getIndexOfParameter(parametersValue, parameterItem.parameter);

                formProviderMethods.setValue(`parameters.${indexOfParameter}.minimalValue`, parameterItem.minimalValue);
            }
            if (parameterItem.maximalValue !== null) {
                const indexOfParameter = getIndexOfParameter(parametersValue, parameterItem.parameter);

                formProviderMethods.setValue(`parameters.${indexOfParameter}.maximalValue`, parameterItem.maximalValue);
            }
        });
        formProviderMethods.setValue(`onlyInStock`, parametersFilterState.onlyInStock);
        if (parametersFilterState.minimalPrice !== null) {
            formProviderMethods.setValue(`minimalPrice`, parametersFilterState.minimalPrice);
        }
        if (parametersFilterState.maximalPrice !== null) {
            formProviderMethods.setValue(`maximalPrice`, parametersFilterState.maximalPrice);
        }
    });

    return (
        <FormProvider {...formProviderMethods}>
            <SelectedParameters productFilterOptions={productFilterOptions} />
            <FilterStyled data-testid={TEST_IDENTIFIER}>
                <Form>
                    <FilterGroupPrice
                        title={t('Price')}
                        minimalPrice={productFilterOptions.minimalPrice}
                        maximalPrice={productFilterOptions.maximalPrice}
                        isOpen={true}
                    />

                    <FilterGroupInStock
                        title={t('Availability')}
                        inStockCount={productFilterOptions.inStock}
                        isOpen={true}
                    />

                    {productFilterOptions.flags.length > 0 && (
                        <FilterGroup
                            title={t('Flags')}
                            filterField="flags"
                            data={productFilterOptions.flags}
                            isOpen={true}
                        />
                    )}

                    {productFilterOptions.brands.length > 0 && (
                        <FilterGroup
                            title={t('Brands')}
                            filterField="brands"
                            data={productFilterOptions.brands}
                            isOpen={true}
                        />
                    )}

                    {productFilterOptions.parameters !== undefined &&
                        fieldsParameters.map((parametersItem, index) => (
                            <FilterGroupParameters
                                key={parametersItem.id}
                                parameterParentUuid={parametersItem.parameterUuid}
                                parameterParentIndex={index}
                                title={parametersItem.parameterName}
                                data={productFilterOptions.parameters?.[index]}
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

/* @component */
export default Filter;
