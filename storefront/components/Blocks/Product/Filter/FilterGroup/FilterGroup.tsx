import {
    FilterGroupArrowStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from './FilterGroup.style';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { FC, useState } from 'react';
import { Controller, useFormContext, useWatch } from 'react-hook-form';
import { BrandsType, FilterFormType, FilterOptionFlagsType } from 'types/productFilter';

type FilterFieldType = 'flags' | 'brands';

type FilterGroupProps = {
    title: string;
    isOpen: boolean;
    filterField: FilterFieldType;
    data?: FilterOptionFlagsType[] | BrandsType[];
};

const getTestIdentifier = (filterField: FilterFieldType) => 'blocks-product-filter-filtergroup-' + filterField;

export const FilterGroup: FC<FilterGroupProps> = ({ title, isOpen, filterField, data }) => {
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
        <FilterGroupStyled data-testid={getTestIdentifier(filterField)}>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {title}
                <FilterGroupArrowStyled alt="" iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
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
                                data-testid={getTestIdentifier(filterField) + '-' + index}
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
