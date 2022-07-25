import { getIndexOfParameter } from 'components/Blocks/Product/Filter/helpers/getIndexOfParameter';
import { getIndexOfParameterValue } from 'components/Blocks/Product/Filter/helpers/getIndexOfParameterValue';
import {
    SelectedParametersListItemRemoveStyled,
    SelectedParametersListItemStyled,
    SelectedParametersListStyled,
    SelectedParametersNameStyled,
} from 'components/Blocks/Product/Filter/SelectedParameters/SelectedParameters.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, Fragment } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { FilterFormParameterType, FilterFormType, FilterOptionsType } from 'types/productFilter';

type ParametersProps = {
    filterOptions: FilterOptionsType;
    checkedParameters: FilterFormParameterType[];
};

const TEST_IDENTIFIER = 'blocks-product-filter-selectedparameters-parameters-';

export const Parameters: FC<ParametersProps> = ({ filterOptions, checkedParameters }) => {
    const t = useTypedTranslationFunction();

    const formProviderMethods = useFormContext<FilterFormType>();
    const parametersValue = useWatch({
        name: 'parameters',
        control: formProviderMethods.control,
    });

    const isMinMaxValueVisible = (
        filteredParameter: FilterFormParameterType,
        valueType: 'minimalValue' | 'maximalValue',
    ) => {
        const parameter = filterOptions.parameters
            ? filterOptions.parameters[getIndexOfParameter(filterOptions.parameters, filteredParameter.parameterUuid)]
            : null;

        return (
            filteredParameter[valueType] !== null &&
            parameter?.__typename === 'ParameterSliderFilterOption' &&
            parameter[valueType] !== filteredParameter[valueType]
        );
    };

    const onUncheckParameter = (parameterUuid: string, parameterValueUuid: string) => () => {
        const indexOfParameter = getIndexOfParameter(filterOptions.parameters ?? [], parameterUuid);
        const indexOfValue = getIndexOfParameterValue(
            filterOptions.parameters ?? [],
            indexOfParameter,
            parameterValueUuid,
        );

        formProviderMethods.setValue(`parameters.${indexOfParameter}.values.${indexOfValue}`, {
            ...parametersValue[indexOfParameter].values[indexOfValue],
            checked: false,
        });
    };

    const onUncheckSliderParameter = (parameterUuid: string) => () => {
        const indexOfParameter = getIndexOfParameter(filterOptions.parameters ?? [], parameterUuid);

        formProviderMethods.setValue(`parameters.${indexOfParameter}`, {
            ...parametersValue[indexOfParameter],
            minimalValue: null,
            maximalValue: null,
        });
    };

    return (
        <>
            {checkedParameters.map((filteredParameter) => (
                <Fragment key={filteredParameter.parameterUuid}>
                    {(isMinMaxValueVisible(filteredParameter, 'minimalValue') ||
                        isMinMaxValueVisible(filteredParameter, 'maximalValue')) && (
                        <SelectedParametersListStyled>
                            <SelectedParametersNameStyled>
                                {filteredParameter.parameterName}:
                            </SelectedParametersNameStyled>
                            <SelectedParametersListItemStyled>
                                {isMinMaxValueVisible(filteredParameter, 'minimalValue') && (
                                    <>
                                        <span>{t('from')}&nbsp;</span>
                                        {filteredParameter.minimalValue}
                                        {filteredParameter.unit?.name !== undefined
                                            ? `\xa0${filteredParameter.unit.name}`
                                            : ''}
                                        {isMinMaxValueVisible(filteredParameter, 'maximalValue') ? ' ' : ''}
                                    </>
                                )}
                                {isMinMaxValueVisible(filteredParameter, 'maximalValue') && (
                                    <>
                                        <span>{t('to')}&nbsp;</span>
                                        {filteredParameter.maximalValue}
                                        {filteredParameter.unit?.name !== undefined
                                            ? `\xa0${filteredParameter.unit.name}`
                                            : ''}
                                    </>
                                )}
                                <SelectedParametersListItemRemoveStyled
                                    iconType="icon"
                                    icon="RemoveThin"
                                    onClick={onUncheckSliderParameter(filteredParameter.parameterUuid)}
                                />
                            </SelectedParametersListItemStyled>
                        </SelectedParametersListStyled>
                    )}
                    {filteredParameter.values.length > 0 && (
                        <SelectedParametersListStyled>
                            <SelectedParametersNameStyled>
                                {filteredParameter.parameterName}:
                            </SelectedParametersNameStyled>
                            {filteredParameter.values.map(
                                (value, index) =>
                                    value.checked && (
                                        <SelectedParametersListItemStyled
                                            key={value.uuid}
                                            data-testid={TEST_IDENTIFIER + index}
                                        >
                                            {value.text}
                                            <SelectedParametersListItemRemoveStyled
                                                iconType="icon"
                                                icon="RemoveThin"
                                                onClick={onUncheckParameter(
                                                    filteredParameter.parameterUuid,
                                                    value.uuid,
                                                )}
                                                data-testid={TEST_IDENTIFIER + 'remove-' + index}
                                            />
                                        </SelectedParametersListItemStyled>
                                    ),
                            )}
                        </SelectedParametersListStyled>
                    )}
                </Fragment>
            ))}
        </>
    );
};
