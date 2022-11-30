import { useCheckedParameters, useFilterState } from 'components/Blocks/Product/Filter/FilterContext/useFilterState';
import { getIndexOfParameter } from 'components/Blocks/Product/Filter/helpers/getIndexOfParameter';
import {
    SelectedParametersListItemRemoveStyled,
    SelectedParametersListItemStyled,
    SelectedParametersListStyled,
    SelectedParametersNameStyled,
} from 'components/Blocks/Product/Filter/SelectedParameters/SelectedParameters.style';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, Fragment, useMemo } from 'react';
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
                                    alt=""
                                    iconType="icon"
                                    icon="RemoveThin"
                                    onClick={() =>
                                        dispatch({
                                            type: 'uncheckSliderParameter',
                                            payload: filteredParameter.parameterUuid,
                                        })
                                    }
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
                                                alt=""
                                                iconType="icon"
                                                icon="RemoveThin"
                                                onClick={() =>
                                                    dispatch({
                                                        type: 'uncheckParameter',
                                                        payload: {
                                                            uuid: filteredParameter.parameterUuid,
                                                            valueUuid: value.uuid,
                                                        },
                                                    })
                                                }
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
