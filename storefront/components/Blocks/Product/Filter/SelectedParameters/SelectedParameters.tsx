import { FC, Fragment } from 'react';
import { FilterFormParametersType, FilterFormType, FilterOptionsType } from 'components/Blocks/Product/Filter/types';
import {
    SelectedParametersListItemRemoveStyled,
    SelectedParametersListItemStyled,
    SelectedParametersListStyled,
    SelectedParametersResetRemoveStyled,
    SelectedParametersResetStyled,
    SelectedParametersResetTextStyled,
    SelectedParametersStyled,
    SelectedParametersTitleStyled,
} from './SelectedParameters.style';
import { useFieldArray, useFormContext } from 'react-hook-form';
import { clearArrayFromEmptyValue } from 'utils/Filter/ClearArrayFromEmptyValue';
import { formatPrice } from 'utils/formatting';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type SelectedParametersProps = {
    productFilterOptions: FilterOptionsType;
    onSubmit: (data: FilterFormType) => void;
};

const SelectedParameters: FC<SelectedParametersProps> = (props) => {
    const t = useTypedTranslationFunction();
    const formProviderMethods = useFormContext<FilterFormType>();
    const parametersFilterState = useShopsysSelector((state) => state.user.parametersFilter);
    const isMinimalPriceChanged =
        parametersFilterState.minimalPrice !== null &&
        props.productFilterOptions.minimalPrice !== parametersFilterState.minimalPrice;
    const isMaximalPriceChanged =
        parametersFilterState.maximalPrice !== null &&
        props.productFilterOptions.maximalPrice !== parametersFilterState.maximalPrice;
    const isOnlyInStock = parametersFilterState !== null && parametersFilterState.onlyInStock;
    const control = formProviderMethods.control;
    const { append, remove } = useFieldArray({ control, name: 'parameters' });

    const getCheckedParameters = () => {
        const arrayOfParameters: string[] = [];

        parametersFilterState.parameters.map((parameterItem: FilterFormParametersType) =>
            parameterItem.values.map((parameterValue) =>
                arrayOfParameters.push(parameterItem.parameter + parameterValue),
            ),
        );

        return arrayOfParameters;
    };

    const onResetAllParameters = () => {
        formProviderMethods.reset();
        onResetPrices();
        props.onSubmit(formProviderMethods.getValues());
    };

    const onResetParameter = (parameterParentUuid: string, value: string) => {
        const formParametersValues = formProviderMethods.getValues().parameters;
        const findedExistingValue = formParametersValues.find(
            (item) => item.parameter === parameterParentUuid,
        ) as FilterFormParametersType;
        const isOnce = findedExistingValue !== undefined && findedExistingValue.values.length === 1;
        const indexOfParameter = formParametersValues.findIndex((item) => item.parameter === parameterParentUuid);

        if (isOnce) {
            remove(indexOfParameter);
        } else {
            const arrayOfValues = [...findedExistingValue.values];
            const indexOfRemovedValue = arrayOfValues.indexOf(value);
            arrayOfValues.splice(indexOfRemovedValue, 1);
            remove(indexOfParameter);
            append({ parameter: parameterParentUuid, values: [...arrayOfValues] });
            clearArrayFromEmptyValue(formProviderMethods);
        }

        props.onSubmit(formProviderMethods.getValues());
    };

    const onResetFlagsAndBrands = (uuid: string, filterField: keyof FilterFormType) => {
        const formValues = formProviderMethods.getValues()[filterField] as string[];
        const formValueIndex = formValues.indexOf(uuid);
        const arrayOfValues = [...formValues];
        arrayOfValues.splice(formValueIndex, 1);

        formProviderMethods.setValue(filterField, [...arrayOfValues]);
        props.onSubmit(formProviderMethods.getValues());
    };

    const onResetPrices = () => {
        formProviderMethods.setValue('minimalPrice', props.productFilterOptions.minimalPrice);
        formProviderMethods.setValue('maximalPrice', props.productFilterOptions.maximalPrice);
        props.onSubmit(formProviderMethods.getValues());
    };

    return (
        <>
            {parametersFilterState !== null &&
                (parametersFilterState.flags.length > 0 ||
                    parametersFilterState.brands.length > 0 ||
                    parametersFilterState.parameters.length > 0 ||
                    isMinimalPriceChanged ||
                    isMaximalPriceChanged ||
                    isOnlyInStock) && (
                    <SelectedParametersStyled>
                        <SelectedParametersTitleStyled type="h4">{t('Selected filters')}</SelectedParametersTitleStyled>
                        <SelectedParametersListStyled>
                            {props.productFilterOptions.brands !== null &&
                                props.productFilterOptions.brands.map((brand, brandIndex) => (
                                    <Fragment key={brandIndex}>
                                        {parametersFilterState.brands.includes(brand.item.uuid) && (
                                            <SelectedParametersListItemStyled>
                                                {brand.item.name}
                                                <SelectedParametersListItemRemoveStyled
                                                    icon="RemoveThin"
                                                    onClick={() => onResetFlagsAndBrands(brand.item.uuid, 'brands')}
                                                />
                                            </SelectedParametersListItemStyled>
                                        )}
                                    </Fragment>
                                ))}

                            {props.productFilterOptions.flags !== null &&
                                props.productFilterOptions.flags.map((flag, flagIndex) => (
                                    <Fragment key={flagIndex}>
                                        {parametersFilterState.flags.includes(flag.item.uuid) && (
                                            <SelectedParametersListItemStyled>
                                                {flag.item.name}
                                                <SelectedParametersListItemRemoveStyled
                                                    icon="RemoveThin"
                                                    onClick={() => onResetFlagsAndBrands(flag.item.uuid, 'flags')}
                                                />
                                            </SelectedParametersListItemStyled>
                                        )}
                                    </Fragment>
                                ))}

                            {props.productFilterOptions.parameters.map((parameterItem, parameterItemIndex) => (
                                <Fragment key={parameterItemIndex}>
                                    {parameterItem.items.map((parameterValue, parameterValueIndex) => (
                                        <Fragment key={parameterValueIndex}>
                                            {getCheckedParameters().includes(
                                                parameterItem.uuid + parameterValue.uuid,
                                            ) && (
                                                <SelectedParametersListItemStyled>
                                                    {parameterValue.text}
                                                    <SelectedParametersListItemRemoveStyled
                                                        iconType="icon"
                                                        icon="RemoveThin"
                                                        onClick={() =>
                                                            onResetParameter(parameterItem.uuid, parameterValue.uuid)
                                                        }
                                                    />
                                                </SelectedParametersListItemStyled>
                                            )}
                                        </Fragment>
                                    ))}
                                </Fragment>
                            ))}

                            {isOnlyInStock !== false && (
                                <SelectedParametersListItemStyled>
                                    {t('Only goods in stock')}
                                    <SelectedParametersListItemRemoveStyled
                                        iconType="icon"
                                        icon="RemoveThin"
                                        onClick={() => {
                                            formProviderMethods.setValue('onlyInStock', false);
                                            props.onSubmit(formProviderMethods.getValues());
                                        }}
                                    />
                                </SelectedParametersListItemStyled>
                            )}

                            {(isMinimalPriceChanged || isMaximalPriceChanged) && (
                                <SelectedParametersListItemStyled>
                                    {isMinimalPriceChanged && (
                                        <>
                                            <span>{t('from')}&nbsp;</span>
                                            {parametersFilterState.minimalPrice !== null &&
                                                formatPrice(
                                                    parametersFilterState.minimalPrice,
                                                    props.productFilterOptions.currencyCode,
                                                    t,
                                                )}
                                            &nbsp;
                                        </>
                                    )}
                                    {isMaximalPriceChanged && (
                                        <>
                                            <span>{t('to')}&nbsp;</span>
                                            {parametersFilterState.maximalPrice !== null &&
                                                formatPrice(
                                                    parametersFilterState.maximalPrice,
                                                    props.productFilterOptions.currencyCode,
                                                    t,
                                                )}
                                            &nbsp;
                                        </>
                                    )}
                                    <SelectedParametersListItemRemoveStyled
                                        iconType="icon"
                                        icon="RemoveThin"
                                        onClick={() => onResetPrices()}
                                    />
                                </SelectedParametersListItemStyled>
                            )}
                        </SelectedParametersListStyled>
                        <SelectedParametersResetStyled onClick={onResetAllParameters}>
                            <SelectedParametersResetTextStyled>{t('Clear all')}</SelectedParametersResetTextStyled>
                            <SelectedParametersResetRemoveStyled iconType="icon" icon="Remove" />
                        </SelectedParametersResetStyled>
                    </SelectedParametersStyled>
                )}
        </>
    );
};

export default SelectedParameters;
