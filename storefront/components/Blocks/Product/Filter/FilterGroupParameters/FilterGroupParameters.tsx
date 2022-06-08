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
import { FC, useState } from 'react';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';
import { FilterFormType, ParametersCheckboxType, ParametersColorType, ParametersType } from 'types/productFilter';

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

    const [isGroupCollapsed, setIsGroupCollapsed] = useState(props.isDefaultCollapsed);
    const formProviderMethods = useFormContext<FilterFormType>();
    const { fields } = useFieldArray({
        control: formProviderMethods.control,
        name: `parameters.${props.parameterParentIndex}.values`,
    });
    const handleGroupClick = () => {
        setIsGroupCollapsed(!isGroupCollapsed);
    };

    return (
        <FilterGroupStyled data-testid={testIdentifier}>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {props.title}
                <FilterGroupArrowStyled iconType="icon" icon="Arrow" isOpen={!isGroupCollapsed} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={!isGroupCollapsed}>
                {props.data?.__typename === 'ParameterCheckboxFilterOption' &&
                    fields.map((dataItem, index) => (
                        <Controller
                            key={dataItem.id}
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
                                        id={field.name}
                                        label={dataItem.text}
                                        fieldRef={field}
                                        count={(props.data as ParametersCheckboxType).values[index]?.count}
                                    />
                                </FilterGroupContentItemStyled>
                            )}
                        />
                    ))}
                {props.data?.__typename === 'ParameterColorFilterOption' && (
                    <FilterGroupColorStyled>
                        {fields.map((dataItem, index) => (
                            <ColorPicker
                                key={dataItem.id}
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
