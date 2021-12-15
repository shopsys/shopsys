import { Controller, useFieldArray, useFormContext } from 'react-hook-form';
import { FC, useState } from 'react';
import { FilterFormType, FilterOptionsParameterTypeEnum, ParametersType } from 'types/productFilter';
import {
    FilterGroupArrowStyled,
    FilterGroupColorStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import Checkbox from 'components/Forms/Checkbox';
import ColorPicker from './ColorPicker';

type FilterGroupParametersProps = {
    /**
     * Group title with arrow
     */
    title: string;
    /**
     * Sets if group is default open
     */
    isOpen: boolean;
    /**
     * parameterParentUuid of parameters
     */
    parameterParentUuid: string;
    /**
     * parameterParentIndex of parameters
     */
    parameterParentIndex: number;
    /**
     * Changes filter items to its elements
     */
    type: FilterOptionsParameterTypeEnum;
    /**
     * Parameters data of product filter
     */
    data?: ParametersType;
};

const FilterGroupParameters: FC<FilterGroupParametersProps> = (props) => {
    const [isGroupOpen, setIsGroupOpen] = useState(props.isOpen);
    const formProviderMethods = useFormContext<FilterFormType>();
    const { fields } = useFieldArray({
        control: formProviderMethods.control,
        name: `parameters.${props.parameterParentIndex}.values`,
    });
    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    return (
        <FilterGroupStyled>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {props.title}
                <FilterGroupArrowStyled iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                {props.type === FilterOptionsParameterTypeEnum.Checkbox &&
                    fields.map((dataItem, index) => (
                        <Controller
                            key={dataItem.id}
                            name={`parameters.${props.parameterParentIndex}.values.${index}.checked`}
                            render={({ field }) => (
                                <FilterGroupContentItemStyled
                                    key={dataItem.uuid}
                                    isDisabled={props.data?.values[index]?.count === 0}
                                    isActive={field.value}
                                >
                                    <Checkbox
                                        name={field.name}
                                        id={field.name}
                                        label={dataItem.text}
                                        fieldRef={field}
                                        count={props.data?.values[index]?.count}
                                    />
                                </FilterGroupContentItemStyled>
                            )}
                        />
                    ))}
                {props.type === FilterOptionsParameterTypeEnum.ColorPicker && (
                    <FilterGroupColorStyled>
                        {fields.map((dataItem, index) => (
                            <ColorPicker
                                key={dataItem.id}
                                parameterParentIndex={props.parameterParentIndex}
                                parameterParentUuid={props.parameterParentUuid}
                                dataItem={dataItem}
                                index={index}
                                isDisabled={props.data?.values[index]?.count === 0}
                            />
                        ))}
                    </FilterGroupColorStyled>
                )}
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};

export default FilterGroupParameters;
