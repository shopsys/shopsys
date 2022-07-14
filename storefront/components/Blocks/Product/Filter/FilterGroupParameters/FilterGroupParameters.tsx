import ColorPicker from './ColorPicker';
import SliderFilter from './SliderFilter';
import {
    FilterGroupArrowStyled,
    FilterGroupColorStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import Checkbox from 'components/Forms/Checkbox';
import { FC, useCallback, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { ParametersCheckboxType, ParametersCheckboxValuesType, ParametersType } from 'types/productFilter';

type FilterGroupParametersProps = {
    /**
     * Group title with arrow
     */
    title: string;
    /**
     * Sets if group is default open
     */
    isDefaultCollapsed: boolean;
    /**
     * parameterParentIndex of parameters
     */
    parameterParentIndex: number;
    /**
     * Parameters data of product filter
     */
    data?: ParametersType;
};

const TEST_IDENTIFIER = (parameterParentIndex: number) =>
    'blocks-product-filter-filtergroup-parameters-' + parameterParentIndex;

const FilterGroupParameters: FC<FilterGroupParametersProps> = ({
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
        <FilterGroupStyled data-testid={TEST_IDENTIFIER(parameterParentIndex)}>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {title}
                <FilterGroupArrowStyled iconType="icon" icon="Arrow" isOpen={!isGroupCollapsed} />
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
                                    data-testid={TEST_IDENTIFIER(parameterParentIndex) + '-' + index}
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
                            <ColorPicker
                                key={dataItem.uuid}
                                parameterParentIndex={parameterParentIndex}
                                dataItem={dataItem}
                                valueIndex={index}
                                isDisabled={data.values[index]?.count === 0}
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

export default FilterGroupParameters;
