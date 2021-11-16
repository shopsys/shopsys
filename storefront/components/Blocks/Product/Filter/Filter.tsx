import { FC, useEffect, useState } from 'react';
import { FilterFormType, FilterOptionsType } from './types';
import { FormProvider, useFieldArray, useWatch } from 'react-hook-form';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import FilterGroup from './FilterGroup';
import FilterGroupInStock from './FilterGroupInStock';
import FilterGroupParameters from './FilterGroupParameters';
import FilterGroupPrice from './FilterGroupPrice';
import { FilterStyled } from './Filter.style';
import { userActions } from 'redux/slices/user';
import Form from 'components/Forms/Form';
import { optionsFilterActions } from 'redux/slices/optionsFilter';
import SelectedParameters from './SelectedParameters';
import { useComponentUpdate } from 'hooks/helpers/UseComponentUpdate';
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
    const [isNotFirstRender, setIsNotFirstRender] = useState(false);
    const parametersFilterState = useShopsysSelector((state) => state.user.parametersFilter);

    const getBrandsValues =
        props.productFilterOptions.brands !== null && props.productFilterOptions.brands !== undefined
            ? props.productFilterOptions.brands.map((value) => ({
                  checked: false,
                  uuid: value.item.uuid,
                  name: value.item.name,
              }))
            : [];

    const getFlagsValues =
        props.productFilterOptions.flags !== null && props.productFilterOptions.flags !== undefined
            ? props.productFilterOptions.flags.map((value) => ({
                  checked: false,
                  uuid: value.item.uuid,
                  name: value.item.name,
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

    useEffect(() => {
        if (isNotFirstRender) {
            const parameters = parametersValue
                .filter((item) => item.values.some((itemChild) => itemChild.checked === true))
                .map((item) => ({
                    parameter: item.parameterUuid,
                    values: item.values.filter((item) => item.checked === true).map((item) => item.uuid),
                }));

            dispatch(userActions.setParametersFilter(parameters));
        } else {
            setIsNotFirstRender(true);
        }
    }, [parametersValue]);

    useEffect(() => {
        if (isNotFirstRender) {
            const brands = brandsValue.filter((item) => item.checked === true).map((item) => item.uuid);

            dispatch(userActions.setBrandsFilter(brands));
        } else {
            setIsNotFirstRender(true);
        }
    }, [brandsValue]);

    useEffect(() => {
        if (isNotFirstRender) {
            const flags = flagsValue.filter((item) => item.checked === true).map((item) => item.uuid);

            dispatch(userActions.setFlagsFilter(flags));
        } else {
            setIsNotFirstRender(true);
        }
    }, [flagsValue]);

    useEffect(() => {
        if (isNotFirstRender) {
            dispatch(userActions.setOnlyInStockFilter(onlyInStockValue));
        } else {
            setIsNotFirstRender(true);
        }
    }, [onlyInStockValue]);

    useEffect(() => {
        if (isNotFirstRender) {
            dispatch(userActions.setMinimalPriceFilter(minimalPriceValue));
        } else {
            setIsNotFirstRender(true);
        }
    }, [minimalPriceValue]);

    useEffect(() => {
        if (isNotFirstRender) {
            dispatch(userActions.setMaximalPriceFilter(maximalPriceValue));
        } else {
            setIsNotFirstRender(true);
        }
    }, [maximalPriceValue]);

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

                    {props.productFilterOptions.flags !== null && props.productFilterOptions.flags !== undefined && (
                        <FilterGroup
                            title={t('Flags')}
                            filterField="flags"
                            data={props.productFilterOptions.flags}
                            isOpen={true}
                        />
                    )}

                    {props.productFilterOptions.brands !== null && props.productFilterOptions.brands !== undefined && (
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
