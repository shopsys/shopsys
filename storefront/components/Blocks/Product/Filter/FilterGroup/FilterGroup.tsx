import {
    FilterGroupArrowStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from './FilterGroup.style';
import Checkbox from 'components/Forms/Checkbox';
import { FC, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { BrandsType, FilterFormType, FilterOptionFlagsType } from 'types/productFilter';

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

const TEST_IDENTIFIER = (filterField: FilterGroupProps['filterField']) =>
    'blocks-product-filter-filtergroup-' + filterField;

const FilterGroup: FC<FilterGroupProps> = ({ title, isOpen, filterField, data }) => {
    const [isGroupOpen, setIsGroupOpen] = useState(isOpen);
    const formProviderMethods = useFormContext<FilterFormType>();

    const filterGroupValue = useWatch({
        name: filterField,
        control: formProviderMethods.control,
    });

    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    return (
        <FilterGroupStyled data-testid={TEST_IDENTIFIER(filterField)}>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {title}
                <FilterGroupArrowStyled iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                {filterGroupValue.map((dataItem, index) => (
                    <Controller
                        name={`${filterField}.${index}.checked`}
                        key={dataItem.uuid}
                        render={({ field }) => (
                            <FilterGroupContentItemStyled
                                isDisabled={data?.[index]?.count === 0}
                                isActive={field.value}
                                data-testid={TEST_IDENTIFIER(filterField) + '-' + index}
                            >
                                <Checkbox
                                    name={field.name}
                                    label={dataItem.name}
                                    fieldRef={field}
                                    count={data?.[index]?.count}
                                />
                            </FilterGroupContentItemStyled>
                        )}
                    />
                ))}
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};

/* @component */
export default FilterGroup;
