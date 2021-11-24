import { FC, useEffect } from 'react';
import { FilterFormType, FilterOptionsType } from './types';
import { FormProvider, useFieldArray, useWatch } from 'react-hook-form';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import FilterGroup from './FilterGroup';
import FilterGroupInStock from './FilterGroupInStock';
import FilterGroupParameters from './FilterGroupParameters';
import FilterGroupPrice from './FilterGroupPrice';
import { FilterStyled } from './Filter.style';
import Form from 'components/Forms/Form';
import SelectedParameters from './SelectedParameters';
import { useComponentUpdate } from 'hooks/helpers/UseComponentUpdate';
import { userActions } from 'redux/slices/user';
import { useRouter } from 'next/router';
import { useShopsysForm } from 'hooks/forms/UseShopsysForm';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type FilterProps = {
    productFilterOptions: FilterOptionsType;
    slug: string;
};

const Filter: FC<FilterProps> = (props) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const dispatch = useShopsysDispatch();
    const parametersFilterState = useShopsysSelector((state) => state.user.parametersFilter);

    const getBrandsValues =
        props.productFilterOptions.brands !== null && props.productFilterOptions.brands !== undefined
            ? props.productFilterOptions.brands.map((value) => ({
                  ...value.brand,
                  checked: false,
              }))
            : [];

    const getFlagsValues =
        props.productFilterOptions.flags !== null && props.productFilterOptions.flags !== undefined
            ? props.productFilterOptions.flags.map((value) => ({
                  ...value.flag,
                  checked: false,
              }))
            : [];

    const getParametersValues =
        props.productFilterOptions.parameters !== undefined && props.productFilterOptions.parameters !== null
            ? props.productFilterOptions.parameters.map((parameter) => {
                  const valuesData = parameter.values.map((value) => ({
                      ...value,
                      checked: false,
                  }));
                  const parameterArray = {
                      parameterName: parameter.name,
                      parameterUuid: parameter.uuid,
                      type: parameter.type,
                      values: valuesData,
                  };

                  return parameterArray;
              })
            : [];

    const formProviderMethods = useShopsysForm<FilterFormType>(undefined, {
        brands: getBrandsValues,
        flags: getFlagsValues,
        parameters: getParametersValues,
        onlyInStock: false,
        minimalPrice: props.productFilterOptions.minimalPrice,
        maximalPrice: props.productFilterOptions.maximalPrice,
    });

    const { fields: fieldsParameters } = useFieldArray({ control: formProviderMethods.control, name: 'parameters' });
    const brandsValue = useWatch({ name: 'brands', control: formProviderMethods.control });
    const flagsValue = useWatch({ name: 'flags', control: formProviderMethods.control });
    const parametersValue = useWatch({ name: 'parameters', control: formProviderMethods.control });
    const onlyInStockValue = useWatch({ name: 'onlyInStock', control: formProviderMethods.control });
    const minimalPriceValue = useWatch({ name: 'minimalPrice', control: formProviderMethods.control });
    const maximalPriceValue = useWatch({ name: 'maximalPrice', control: formProviderMethods.control });

    useComponentUpdate(() => {
        const parameters = [];

        for (const parameter of parametersValue) {
            const checkedValues = [];

            for (const value of parameter.values) {
                if (value.checked) {
                    checkedValues.push(value.uuid);
                }
            }

            if (checkedValues.length === 0) {
                continue;
            }

            parameters.push({ parameter: parameter.parameterUuid, values: checkedValues });
        }

        dispatch(userActions.setParametersFilter(parameters));
    }, [parametersValue]);

    useComponentUpdate(() => {
        const brands = brandsValue.reduce(function (result: string[], brand) {
            if (brand.checked === true) {
                result.push(brand.uuid);
            }
            return result;
        }, []);

        dispatch(userActions.setBrandsFilter(brands));
    }, [brandsValue]);

    useComponentUpdate(() => {
        const flags = flagsValue.reduce(function (result: string[], flag) {
            if (flag.checked === true) {
                result.push(flag.uuid);
            }
            return result;
        }, []);

        dispatch(userActions.setFlagsFilter(flags));
    }, [flagsValue]);

    useComponentUpdate(() => {
        dispatch(userActions.setOnlyInStockFilter(onlyInStockValue));
    }, [onlyInStockValue]);

    useComponentUpdate(() => {
        dispatch(userActions.setMinimalPriceFilter(minimalPriceValue));
    }, [minimalPriceValue]);

    useComponentUpdate(() => {
        dispatch(userActions.setMaximalPriceFilter(maximalPriceValue));
    }, [maximalPriceValue]);

    useComponentUpdate(() => {
        formProviderMethods.setValue(`brands`, getBrandsValues);
        formProviderMethods.setValue(`flags`, getFlagsValues);
        formProviderMethods.setValue(`parameters`, getParametersValues);
        formProviderMethods.setValue(`onlyInStock`, false);
        formProviderMethods.setValue(`minimalPrice`, props.productFilterOptions.minimalPrice);
        formProviderMethods.setValue(`maximalPrice`, props.productFilterOptions.maximalPrice);
    }, [props.slug]);

    useEffect(() => {
        const queryParams = router.query;

        if (
            parametersFilterState.brands.length === 0 &&
            parametersFilterState.flags.length === 0 &&
            parametersFilterState.parameters.length === 0 &&
            parametersFilterState.onlyInStock === false &&
            (parametersFilterState.minimalPrice === props.productFilterOptions.minimalPrice ||
                parametersFilterState.minimalPrice === null) &&
            (parametersFilterState.maximalPrice === props.productFilterOptions.maximalPrice ||
                parametersFilterState.maximalPrice === null)
        ) {
            delete queryParams.filter;
        } else {
            queryParams.filter = JSON.stringify(parametersFilterState);
        }

        router.replace({ query: queryParams }, undefined, { scroll: false });
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
        const indexOfParameter = parametersValue.findIndex((parameter) => parameter.parameterUuid === parameterUuid);
        const indexOfValue = parametersValue[indexOfParameter].values.findIndex(
            (value) => value.uuid === parameterValueUuid,
        );

        formProviderMethods.setValue(`parameters.${indexOfParameter}.values.${indexOfValue}.checked`, true);
    };

    // this useEffect triggered only on the first render
    useEffect(() => {
        parametersFilterState.brands.forEach((brandUuid) => onBrandCheck(brandUuid));
        parametersFilterState.flags.forEach((flagUuid) => onFlagCheck(flagUuid));
        parametersFilterState.parameters.forEach((parameterItem) =>
            parameterItem.values.forEach((parameterValueUuid) =>
                onParameterCheck(parameterItem.parameter, parameterValueUuid),
            ),
        );
        formProviderMethods.setValue(`onlyInStock`, parametersFilterState.onlyInStock);
        if (parametersFilterState.minimalPrice !== null) {
            formProviderMethods.setValue(`minimalPrice`, parametersFilterState.minimalPrice);
        }
        if (parametersFilterState.maximalPrice !== null) {
            formProviderMethods.setValue(`maximalPrice`, parametersFilterState.maximalPrice);
        }
    }, []);

    return (
        <FormProvider {...formProviderMethods}>
            <SelectedParameters productFilterOptions={props.productFilterOptions} slug={props.slug} />
            <FilterStyled>
                <Form>
                    <FilterGroupPrice
                        title={t('Price')}
                        minimalPrice={props.productFilterOptions.minimalPrice}
                        maximalPrice={props.productFilterOptions.maximalPrice}
                        isOpen={true}
                    />

                    <FilterGroupInStock
                        title={t('Availability')}
                        inStockCount={props.productFilterOptions.inStock}
                        isOpen={true}
                    />

                    {props.productFilterOptions.flags.length > 0 && (
                        <FilterGroup
                            title={t('Flags')}
                            filterField="flags"
                            data={props.productFilterOptions.flags}
                            isOpen={true}
                        />
                    )}

                    {props.productFilterOptions.brands.length > 0 && (
                        <FilterGroup
                            title={t('Brands')}
                            filterField="brands"
                            data={props.productFilterOptions.brands}
                            isOpen={true}
                        />
                    )}

                    {props.productFilterOptions.parameters !== undefined &&
                        props.productFilterOptions.parameters !== null &&
                        fieldsParameters.map((parametersItem, index) => (
                            <FilterGroupParameters
                                key={parametersItem.id}
                                parameterParentUuid={parametersItem.parameterUuid}
                                parameterParentIndex={index}
                                title={parametersItem.parameterName}
                                type={parametersItem.type}
                                data={props.productFilterOptions.parameters?.[index]}
                                isOpen={true}
                            />
                        ))}
                </Form>
            </FilterStyled>
        </FormProvider>
    );
};

/* @component */
export default Filter;
