import { getIndexOfParameter } from 'components/Blocks/Product/Filter/helpers/getIndexOfParameter';
import { getIndexOfParameterValue } from 'components/Blocks/Product/Filter/helpers/getIndexOfParameterValue';
import {
    SelectedParametersListItemRemoveStyled,
    SelectedParametersListItemStyled,
} from 'components/Blocks/Product/Filter/SelectedParameters/SelectedParameters.style';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useEffect, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { FilterFormParameterType, FilterFormType, FilterOptionsType } from 'types/productFilter';

type ParametersProps = {
    filterOptions: FilterOptionsType;
};

const TEST_IDENTIFIER = 'blocks-product-filter-selectedparameters-parameters-';

const Parameters: FC<ParametersProps> = ({ filterOptions }) => {
    const t = useTypedTranslationFunction();

    const formProviderMethods = useFormContext<FilterFormType>();
    const [filteredParameters, setFilteredParameters] = useState<FilterFormParameterType[]>([]);
    const parametersFilterState = useShopsysSelector((state) => state.optionsFilter);
    const parametersValue = useWatch({
        name: 'parameters',
        control: formProviderMethods.control,
    });

    const isMinMaxValueVisible = (
        filteredParameter: FilterFormParameterType,
        valueType: 'minimalValue' | 'maximalValue',
    ) => {
        const parameter = filterOptions.parameters
            ? filterOptions.parameters[getIndexOfParameter(parametersValue, filteredParameter.parameterUuid)]
            : null;

        return (
            filteredParameter[valueType] !== undefined &&
            parameter?.__typename === 'ParameterSliderFilterOption' &&
            parameter[valueType] !== filteredParameter[valueType]
        );
    };

    const onUncheckParameter = (parameterUuid: string, parameterValueUuid: string) => {
        const indexOfParameter = getIndexOfParameter(parametersValue, parameterUuid);
        const indexOfValue = getIndexOfParameterValue(parametersValue, indexOfParameter, parameterValueUuid);

        formProviderMethods.setValue(`parameters.${indexOfParameter}.values.${indexOfValue}.checked`, false);
    };

    const onUncheckSliderParameter = (parameterUuid: string, type: 'minimalValue' | 'maximalValue') => {
        const indexOfParameter = getIndexOfParameter(parametersValue, parameterUuid);

        formProviderMethods.setValue(`parameters.${indexOfParameter}.${type}`, undefined);
    };

    useEffect(() => {
        const updatedFilteredParameters = [];

        for (const parameter of parametersValue) {
            if (
                parametersFilterState.parameters.some(
                    (stateParameter) => stateParameter.parameter === parameter.parameterUuid,
                )
            ) {
                updatedFilteredParameters.push(parameter);
            }
        }

        setFilteredParameters(updatedFilteredParameters);
    }, [parametersFilterState.parameters, parametersValue]);

    return (
        <>
            {filteredParameters.map((filteredParameter) => (
                <>
                    {isMinMaxValueVisible(filteredParameter, 'minimalValue') && (
                        <SelectedParametersListItemStyled data-testid={TEST_IDENTIFIER + 'from'}>
                            {t('from')} {filteredParameter.minimalValue} {filteredParameter.unit?.name}
                            <SelectedParametersListItemRemoveStyled
                                iconType="icon"
                                icon="RemoveThin"
                                onClick={() =>
                                    onUncheckSliderParameter(filteredParameter.parameterUuid, 'minimalValue')
                                }
                                data-testid={TEST_IDENTIFIER + 'remove-from'}
                            />
                        </SelectedParametersListItemStyled>
                    )}
                    {isMinMaxValueVisible(filteredParameter, 'maximalValue') && (
                        <SelectedParametersListItemStyled data-testid={TEST_IDENTIFIER + 'from'}>
                            {t('to')} {filteredParameter.maximalValue} {filteredParameter.unit?.name}
                            <SelectedParametersListItemRemoveStyled
                                iconType="icon"
                                icon="RemoveThin"
                                onClick={() =>
                                    onUncheckSliderParameter(filteredParameter.parameterUuid, 'maximalValue')
                                }
                                data-testid={TEST_IDENTIFIER + 'remove-from'}
                            />
                        </SelectedParametersListItemStyled>
                    )}
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
                                        onClick={() => onUncheckParameter(filteredParameter.parameterUuid, value.uuid)}
                                        data-testid={TEST_IDENTIFIER + 'remove-' + index}
                                    />
                                </SelectedParametersListItemStyled>
                            ),
                    )}
                </>
            ))}
        </>
    );
};

export default Parameters;
