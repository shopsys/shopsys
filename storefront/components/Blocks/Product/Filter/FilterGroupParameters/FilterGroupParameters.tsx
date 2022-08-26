import { SliderFilter } from './SliderFilter/SliderFilter';
import {
    FilterGroupArrowStyled,
    FilterGroupColorStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { CheckboxColor } from 'components/Forms/CheckboxColor/CheckboxColor';
import { FC, useCallback, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { ParametersCheckboxType, ParametersCheckboxValuesType, ParametersType } from 'types/productFilter';

type FilterGroupParametersProps = {
    title: string;
    isDefaultCollapsed: boolean;
    parameterParentIndex: number;
    data?: ParametersType;
};

const getTestIdentifier = (parameterParentIndex: number) =>
    'blocks-product-filter-filtergroup-parameters-' + parameterParentIndex;

export const FilterGroupParameters: FC<FilterGroupParametersProps> = ({
    title,
    isDefaultCollapsed,
    parameterParentIndex,
    data,
}) => {
    const formProviderMethods = useFormContext();
    const parameterValue = useWatch({
        control: formProviderMethods.control,
        name: `parameters.${parameterParentIndex}.values`,
    });
    const [isGroupCollapsed, setIsGroupCollapsed] = useState(isDefaultCollapsed);

    const handleGroupClick = () => {
        setIsGroupCollapsed(!isGroupCollapsed);
    };

    const onChangeParameterValueHandler = useCallback(
        (dataItem: ParametersCheckboxValuesType, index: number) => () => {
            formProviderMethods.setValue(`parameters.${parameterParentIndex}.values.${index}`, {
                ...dataItem,
                checked: !(parameterValue[index].checked as boolean),
            });
        },
        [formProviderMethods, parameterValue, parameterParentIndex],
    );

    return (
        <FilterGroupStyled data-testid={getTestIdentifier(parameterParentIndex)}>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {title}
                <FilterGroupArrowStyled alt="" iconType="icon" icon="Arrow" isOpen={!isGroupCollapsed} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={!isGroupCollapsed}>
                {data?.__typename === 'ParameterCheckboxFilterOption' &&
                    data.values.map((dataItem, index) => (
                        <Controller
                            key={dataItem.uuid}
                            name={`parameters.${parameterParentIndex}.values.${index}.checked`}
                            render={({ field }) => (
                                <FilterGroupContentItemStyled
                                    key={dataItem.uuid}
                                    isDisabled={data.values[index]?.count === 0}
                                    isActive={field.value}
                                    data-testid={getTestIdentifier(parameterParentIndex) + '-' + index}
                                >
                                    <Checkbox
                                        name={field.name}
                                        label={dataItem.text}
                                        onChange={onChangeParameterValueHandler(dataItem, index)}
                                        value={parameterValue[index].checked}
                                        count={(data as ParametersCheckboxType).values[index]?.count}
                                    />
                                </FilterGroupContentItemStyled>
                            )}
                        />
                    ))}
                {data?.__typename === 'ParameterColorFilterOption' && (
                    <FilterGroupColorStyled>
                        {data.values.map((dataItem, index) => (
                            <Controller
                                key={dataItem.uuid}
                                name={`parameters.${parameterParentIndex}.values.${index}.checked`}
                                render={({ field }) => (
                                    <>
                                        <CheckboxColor
                                            name={field.name}
                                            id={field.name}
                                            bgColor={dataItem.rgbHex ?? undefined}
                                            onChange={onChangeParameterValueHandler(dataItem, index)}
                                            value={parameterValue[index].checked}
                                            data-testid={getTestIdentifier(index)}
                                            label={dataItem.text}
                                        />
                                    </>
                                )}
                            />
                        ))}
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
