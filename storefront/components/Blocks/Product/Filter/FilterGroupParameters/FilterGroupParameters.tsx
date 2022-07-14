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
import {
    ParametersCheckboxType,
    ParametersCheckboxValuesType,
    ParametersColorType,
    ParametersType,
} from 'types/productFilter';

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
     * parameterParentUuid of parameters
     */
    parameterParentUuid: string;
    /**
     * parameterParentIndex of parameters
     */
    parameterParentIndex: number;
    /**
     * Parameters data of product filter
     */
    data?: ParametersType;
};

const FilterGroupParameters: FC<FilterGroupParametersProps> = (props) => {
    const testIdentifier = 'blocks-product-filter-filtergroup-parameters-' + props.parameterParentIndex;
    const formProviderMethods = useFormContext();
    const parameterValue = useWatch({
        control: formProviderMethods.control,
        name: `parameters.${props.parameterParentIndex}.values`,
    });
    const [isGroupCollapsed, setIsGroupCollapsed] = useState(props.isDefaultCollapsed);

    const handleGroupClick = () => {
        setIsGroupCollapsed(!isGroupCollapsed);
    };

    const onChangeParameterValueHandler = useCallback(
        (dataItem: ParametersCheckboxValuesType, index: number) => () => {
            formProviderMethods.setValue(`parameters.${props.parameterParentIndex}.values.${index}`, {
                ...dataItem,
                checked: !(parameterValue[index].checked as boolean),
            });
        },
        [formProviderMethods, parameterValue, props.parameterParentIndex],
    );

    return (
        <FilterGroupStyled data-testid={testIdentifier}>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {props.title}
                <FilterGroupArrowStyled iconType="icon" icon="Arrow" isOpen={!isGroupCollapsed} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={!isGroupCollapsed}>
                {props.data?.__typename === 'ParameterCheckboxFilterOption' &&
                    props.data.values.map((dataItem, index) => (
                        <Controller
                            key={dataItem.uuid}
                            name={`parameters.${props.parameterParentIndex}.values.${index}.checked`}
                            render={({ field }) => (
                                <FilterGroupContentItemStyled
                                    key={dataItem.uuid}
                                    isDisabled={(props.data as ParametersCheckboxType).values[index]?.count === 0}
                                    isActive={field.value}
                                    data-testid={testIdentifier + '-' + index}
                                >
                                    <Checkbox
                                        name={field.name}
                                        label={dataItem.text}
                                        onChange={onChangeParameterValueHandler(dataItem, index)}
                                        value={parameterValue[index].checked}
                                        count={(props.data as ParametersCheckboxType).values[index]?.count}
                                    />
                                </FilterGroupContentItemStyled>
                            )}
                        />
                    ))}
                {props.data?.__typename === 'ParameterColorFilterOption' && (
                    <FilterGroupColorStyled>
                        {props.data.values.map((dataItem, index) => (
                            <ColorPicker
                                key={dataItem.uuid}
                                parameterParentIndex={props.parameterParentIndex}
                                parameterParentUuid={props.parameterParentUuid}
                                dataItem={dataItem}
                                index={index}
                                isDisabled={(props.data as ParametersColorType).values[index]?.count === 0}
                            />
                        ))}
                    </FilterGroupColorStyled>
                )}
                {props.data?.__typename === 'ParameterSliderFilterOption' && (
                    <SliderFilter
                        parameterParentIndex={props.parameterParentIndex}
                        min={props.data.minimalValue}
                        max={props.data.maximalValue}
                    />
                )}
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};

export default FilterGroupParameters;
