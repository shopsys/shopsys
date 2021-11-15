import { FC, useState } from 'react';
import { FilterFormType, ItemsType } from 'components/Blocks/Product/Filter/types';
import {
    FilterGroupArrowStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from './FilterGroup.style';
import Checkbox from 'components/Forms/Checkbox';
import { useFormContext } from 'react-hook-form';

type FilterGroupProps = {
    /**
     * Group title with arrow
     */
    title: string;
    /**
     * Sets if group is default open
     */
    isOpen: boolean;
    /**
     * Special type of parameters for separation
     */
    filterField: 'flags' | 'brands';
    /**
     * Parameters data of product filter
     */
    data: ItemsType[];
    /**
     * Function for submit form
     */
    onSubmit: (data: FilterFormType) => void;
};

const FilterGroup: FC<FilterGroupProps> = (props) => {
    const [isGroupOpen, setIsGroupOpen] = useState(props.isOpen);
    const formProviderMethods = useFormContext<FilterFormType>();

    const onChangeCheckbox = (value: string, filterField: 'flags' | 'brands') => {
        const formValues = formProviderMethods.getValues()[filterField];
        const values = [...formValues];
        const valueIndex = values.indexOf(value);

        if (values.includes(value)) {
            values.splice(valueIndex, 1);
            formProviderMethods.setValue(filterField, [...values]);
        } else {
            formProviderMethods.setValue(filterField, [...values, ...[value]]);
        }

        props.onSubmit(formProviderMethods.getValues());
    };

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
                {props.data.map((dataItem: ItemsType) => (
                    <FilterGroupContentItemStyled
                        key={dataItem.item.uuid}
                        isDisabled={dataItem.count === 0}
                        isActive={formProviderMethods.getValues()[props.filterField].includes(dataItem.item.uuid)}
                    >
                        <Checkbox
                            name={dataItem.item.uuid}
                            id={dataItem.item.uuid}
                            label={dataItem.item.name}
                            count={dataItem.count}
                            checked={formProviderMethods.getValues()[props.filterField].includes(dataItem.item.uuid)}
                            onChange={() => onChangeCheckbox(dataItem.item.uuid, props.filterField)}
                        />
                    </FilterGroupContentItemStyled>
                ))}
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};

/* @component */
export default FilterGroup;
