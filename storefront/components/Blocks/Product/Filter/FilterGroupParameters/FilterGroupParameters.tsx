import { useFilterState } from '../FilterContext/useFilterState';
import { SliderFilter } from './SliderFilter/SliderFilter';
import {
    FilterGroupArrowStyled,
    FilterGroupColorStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
    ShowAllButtonStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { CheckboxColor } from 'components/Forms/CheckboxColor/CheckboxColor';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useMemo, useState } from 'react';
import { ParametersType } from 'types/productFilter';

type FilterGroupParametersProps = {
    title: string;
    isDefaultCollapsed: boolean;
    parameterParentIndex: number;
    data?: ParametersType;
    defaultNumberOfShownParameters: number;
    areByDefaultAllParametersShown: boolean;
};

const getTestIdentifier = (parameterParentIndex: number) =>
    'blocks-product-filter-filtergroup-parameters-' + parameterParentIndex;

export const FilterGroupParameters: FC<FilterGroupParametersProps> = ({
    title,
    isDefaultCollapsed,
    parameterParentIndex,
    data,
    defaultNumberOfShownParameters,
    areByDefaultAllParametersShown,
}) => {
    const t = useTypedTranslationFunction();
    const [isGroupCollapsed, setIsGroupCollapsed] = useState(isDefaultCollapsed);
    const [areAllParametersShown, setAreAllParametersShown] = useState(areByDefaultAllParametersShown);
    const [state, dispatch] = useFilterState();
    const selectedParameters = useMemo(
        () => (state.selected.parameters.length > 0 ? state.selected.parameters[parameterParentIndex] : undefined),
        [parameterParentIndex, state.selected.parameters],
    );
    const parameters = useMemo(
        () => state.options.parameters?.[parameterParentIndex],
        [parameterParentIndex, state.options.parameters],
    );

    return (
        <FilterGroupStyled data-testid={getTestIdentifier(parameterParentIndex)}>
            <FilterGroupTitleStyled
                onClick={() => setIsGroupCollapsed((currentGroupVisibility) => !currentGroupVisibility)}
            >
                {title}
                <FilterGroupArrowStyled alt="" iconType="icon" icon="Arrow" isOpen={!isGroupCollapsed} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={!isGroupCollapsed}>
                {parameters?.__typename === 'ParameterCheckboxFilterOption' && (
                    <>
                        {parameters.values
                            .slice(0, areAllParametersShown ? undefined : defaultNumberOfShownParameters)
                            .map((dataItem, index) => {
                                const item = selectedParameters?.values[index] ?? undefined;
                                const id = `parameters.${parameterParentIndex}.values.${index}.checked`;

                                return (
                                    <FilterGroupContentItemStyled
                                        key={dataItem.uuid}
                                        isDisabled={dataItem.count === 0}
                                        isActive={item?.checked ?? false}
                                        data-testid={getTestIdentifier(parameterParentIndex) + '-' + index}
                                    >
                                        <Checkbox
                                            id={id}
                                            name={id}
                                            label={dataItem.text}
                                            onChange={
                                                item
                                                    ? () =>
                                                          dispatch({
                                                              type: 'setParameter',
                                                              payload: {
                                                                  value: {
                                                                      ...item,
                                                                      checked: !item.checked,
                                                                  },
                                                                  parameterIndex: parameterParentIndex,
                                                                  valueIndex: index,
                                                              },
                                                          })
                                                    : () => void null
                                            }
                                            value={item?.checked ?? false}
                                            count={dataItem.count}
                                        />
                                    </FilterGroupContentItemStyled>
                                );
                            })}
                        {parameters.values.length > defaultNumberOfShownParameters && (
                            <ShowAllButtonStyled onClick={() => setAreAllParametersShown((prev) => !prev)}>
                                {areAllParametersShown ? t('show less') : t('show more')}
                            </ShowAllButtonStyled>
                        )}
                    </>
                )}
                {parameters?.__typename === 'ParameterColorFilterOption' && (
                    <FilterGroupColorStyled>
                        {parameters.values.map((dataItem, index) => {
                            const item = selectedParameters?.values[index] ?? undefined;
                            const id = `parameters.${parameterParentIndex}.values.${index}.checked`;

                            return (
                                <CheckboxColor
                                    key={dataItem.uuid}
                                    bgColor={dataItem.rgbHex ?? undefined}
                                    testIdentifier={getTestIdentifier(index)}
                                    id={id}
                                    name={id}
                                    onChange={
                                        item
                                            ? () =>
                                                  dispatch({
                                                      type: 'setParameter',
                                                      payload: {
                                                          value: {
                                                              ...item,
                                                              checked: !item.checked,
                                                          },
                                                          parameterIndex: parameterParentIndex,
                                                          valueIndex: index,
                                                      },
                                                  })
                                            : () => void null
                                    }
                                    value={item?.checked ?? false}
                                    label={dataItem.text}
                                />
                            );
                        })}
                    </FilterGroupColorStyled>
                )}
                {data?.__typename === 'ParameterSliderFilterOption' && (
                    <SliderFilter
                        parameterParentIndex={parameterParentIndex}
                        min={data.minimalValue}
                        max={data.maximalValue}
                    />
                )}
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};
