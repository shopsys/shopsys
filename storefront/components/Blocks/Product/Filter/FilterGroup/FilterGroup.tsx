import {
    FilterGroupArrowStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from './FilterGroup.style';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { FC, useCallback, useState } from 'react';
import { useController, useFormContext } from 'react-hook-form';
import { BrandsType, FilterFormFlagType, FilterFormType, FilterOptionFlagsType } from 'types/productFilter';

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

    const {
        field: { value: filterGroupValue },
    } = useController({ name: filterField, control: formProviderMethods.control });

    const onChangeCheckboxValueHandler = useCallback(
        (dataItem: FilterFormFlagType, index: number) => () => {
            formProviderMethods.setValue(`${filterField}.${index}`, {
                ...dataItem,
                checked: !filterGroupValue[index].checked,
            });
        },
        [formProviderMethods, filterGroupValue, filterField],
    );

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
                    <FilterGroupContentItemStyled
                        key={dataItem.uuid}
                        isDisabled={data?.[index]?.count === 0}
                        isActive={filterGroupValue[index].checked}
                        data-testid={getTestIdentifier(filterField) + '-' + index}
                    >
                        <Checkbox
                            id={`${filterField}.${index}.checked`}
                            name={`${filterField}.${index}.checked`}
                            label={dataItem.name}
                            onChange={onChangeCheckboxValueHandler(dataItem, index)}
                            value={filterGroupValue[index].checked}
                            count={data?.[index]?.count}
                        />
                    </FilterGroupContentItemStyled>
                ))}
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};
