import { FC, useEffect, useState } from 'react';
import { FilterFormParameterType, FilterFormType } from 'components/Blocks/Product/Filter/types';
import {
    SelectedParametersListItemRemoveStyled,
    SelectedParametersListItemStyled,
} from 'components/Blocks/Product/Filter/SelectedParameters/SelectedParameters.style';
import { useFormContext, useWatch } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';

const Parameters: FC = () => {
    const formProviderMethods = useFormContext<FilterFormType>();
    const [filteredParameters, setFilteredParameters] = useState<FilterFormParameterType[] | []>([]);
    const parametersFilterState = useShopsysSelector((state) => state.optionsFilter);
    const parametersValue = useWatch({
        name: 'parameters',
        control: formProviderMethods.control,
    });

    const onUncheckParameter = (parameterUuid: string, parameterValueUuid: string) => {
        const indexOfParameter = parametersValue.findIndex((item) => item.parameterUuid === parameterUuid);
        const indexOfValue = parametersValue[indexOfParameter]?.values.findIndex(
            (item) => item.uuid === parameterValueUuid,
        );

        formProviderMethods.setValue(`parameters.${indexOfParameter}.values.${indexOfValue}.checked`, false);
    };

    useEffect(() => {
        const updatedFilteredParameters = [];

        for (const parameter of parametersValue) {
            if (
                parametersFilterState.parameters.some(
                    (stateParameter) => stateParameter.parameter === parameter.parameterUuid,
                ) === true
            ) {
                updatedFilteredParameters.push(parameter);
            }
        }

        setFilteredParameters(updatedFilteredParameters);
    }, [parametersFilterState.parameters]);

    return (
        <>
            {filteredParameters.map((filteredParameter) =>
                filteredParameter.values.map(
                    (value) =>
                        value.checked && (
                            <SelectedParametersListItemStyled key={value.uuid}>
                                {value.text}
                                <SelectedParametersListItemRemoveStyled
                                    iconType="icon"
                                    icon="RemoveThin"
                                    onClick={() => onUncheckParameter(filteredParameter.parameterUuid, value.uuid)}
                                />
                            </SelectedParametersListItemStyled>
                        ),
                ),
            )}
        </>
    );
};

export default Parameters;
