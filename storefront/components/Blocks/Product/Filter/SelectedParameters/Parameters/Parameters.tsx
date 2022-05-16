import {
    SelectedParametersListItemRemoveStyled,
    SelectedParametersListItemStyled,
} from 'components/Blocks/Product/Filter/SelectedParameters/SelectedParameters.style';
import { FC, useEffect, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useShopsysSelector } from 'redux/main';
import { FilterFormParameterType, FilterFormType } from 'types/productFilter';

const Parameters: FC = () => {
    const testIdentifier = 'blocks-product-filter-selectedparameters-parameters-';

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
                )
            ) {
                updatedFilteredParameters.push(parameter);
            }
        }

        setFilteredParameters(updatedFilteredParameters);
    }, [parametersFilterState.parameters, parametersValue]);

    return (
        <>
            {filteredParameters.map((filteredParameter) =>
                filteredParameter.values.map(
                    (value, index) =>
                        value.checked && (
                            <SelectedParametersListItemStyled key={value.uuid} data-testid={testIdentifier + index}>
                                {value.text}
                                <SelectedParametersListItemRemoveStyled
                                    iconType="icon"
                                    icon="RemoveThin"
                                    onClick={() => onUncheckParameter(filteredParameter.parameterUuid, value.uuid)}
                                    data-testid={testIdentifier + 'remove-' + index}
                                />
                            </SelectedParametersListItemStyled>
                        ),
                ),
            )}
        </>
    );
};

export default Parameters;
