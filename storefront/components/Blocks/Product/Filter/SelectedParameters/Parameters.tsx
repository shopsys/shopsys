import { SelectedParametersList, SelectedParametersListItem, SelectedParametersName } from '../FilterElements';
import { SelectedParametersIcon } from './SelectedParametersIcon';
import { useCheckedParameters, useFilterState } from 'components/Blocks/Product/Filter/FilterContext/useFilterState';
import { getIndexOfParameter } from 'components/Blocks/Product/Filter/helpers/getIndexOfParameter';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { Fragment, useMemo } from 'react';
import { FilterFormParameterType } from 'types/productFilter';

const TEST_IDENTIFIER = 'blocks-product-filter-selectedparameters-parameters-';

export const Parameters: FC = () => {
    const t = useTypedTranslationFunction();
    const checkedParameters = useCheckedParameters();
    const [state, dispatch] = useFilterState();
    const filterOptions = useMemo(() => state.options, [state.options]);

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

    return (
        <>
            {checkedParameters.map((filteredParameter) => (
                <Fragment key={filteredParameter.parameterUuid}>
                    {(isMinMaxValueVisible(filteredParameter, 'minimalValue') ||
                        isMinMaxValueVisible(filteredParameter, 'maximalValue')) && (
                        <SelectedParametersList>
                            <SelectedParametersName>{filteredParameter.parameterName}:</SelectedParametersName>
                            <SelectedParametersListItem>
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
                                <SelectedParametersIcon
                                    onClick={() =>
                                        dispatch({
                                            type: 'uncheckSliderParameter',
                                            payload: filteredParameter.parameterUuid,
                                        })
                                    }
                                />
                            </SelectedParametersListItem>
                        </SelectedParametersList>
                    )}
                    {filteredParameter.values.length > 0 && (
                        <SelectedParametersList>
                            <SelectedParametersName>{filteredParameter.parameterName}:</SelectedParametersName>
                            {filteredParameter.values.map(
                                (value, index) =>
                                    value.checked && (
                                        <SelectedParametersListItem
                                            key={value.uuid}
                                            dataTestId={TEST_IDENTIFIER + index}
                                        >
                                            {value.text}
                                            <SelectedParametersIcon
                                                onClick={() =>
                                                    dispatch({
                                                        type: 'uncheckParameter',
                                                        payload: {
                                                            uuid: filteredParameter.parameterUuid,
                                                            valueUuid: value.uuid,
                                                        },
                                                    })
                                                }
                                                dataTestId={TEST_IDENTIFIER + 'remove-' + index}
                                            />
                                        </SelectedParametersListItem>
                                    ),
                            )}
                        </SelectedParametersList>
                    )}
                </Fragment>
            ))}
        </>
    );
};
