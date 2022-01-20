import { BrandsType, FilterFormType, FilterOptionFlagsType } from 'types/productFilter';
import { Controller, useFieldArray, useFormContext } from 'react-hook-form';
import { FC, Fragment, useState } from 'react';
import {
    FilterGroupArrowStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from './FilterGroup.style';
import Checkbox from 'components/Forms/Checkbox';

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
    data?: FilterOptionFlagsType[] | BrandsType[];
};

const FilterGroup: FC<FilterGroupProps> = (props) => {
    const testIdentifier = 'blocks-product-filter-filtergroup-' + props.filterField;

    const [isGroupOpen, setIsGroupOpen] = useState(props.isOpen);
    const formProviderMethods = useFormContext<FilterFormType>();
    const { fields } = useFieldArray({
        control: formProviderMethods.control,
        name: props.filterField,
    });

    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    return (
        <FilterGroupStyled data-testid={testIdentifier}>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {props.title}
                <FilterGroupArrowStyled iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                {fields.map((dataItem, index) => (
                    <Fragment key={dataItem.id}>
                        <Controller
                            name={`${props.filterField}.${index}.checked`}
                            render={({ field }) => (
                                <FilterGroupContentItemStyled
                                    key={dataItem.uuid}
                                    isDisabled={props.data?.[index]?.count === 0}
                                    isActive={field.value}
                                    data-testid={testIdentifier + '-' + index}
                                >
                                    <Checkbox
                                        name={field.name}
                                        id={field.name}
                                        label={dataItem.name}
                                        fieldRef={field}
                                        count={props.data?.[index]?.count}
                                    />
                                </FilterGroupContentItemStyled>
                            )}
                        />
                    </Fragment>
                ))}
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};

/* @component */
export default FilterGroup;
