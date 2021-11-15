import { Controller, useFormContext } from 'react-hook-form';
import { FC, useState } from 'react';
import {
    FilterGroupArrowStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import Checkbox from 'components/Forms/Checkbox';
import { FilterFormType } from 'components/Blocks/Product/Filter/types';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';

type FilterGroupInStockProps = {
    /**
     * Group title with arrow
     */
    title: string;
    /**
     * Sets if group is default open
     */
    isOpen: boolean;
    /**
     * Count of inStock parameter
     */
    inStockCount: number;
    /**
     * Function for submit form
     */
    onSubmit: (data: FilterFormType) => void;
};

const FilterGroupInStock: FC<FilterGroupInStockProps> = (props) => {
    const t = useTypedTranslationFunction();
    const [isGroupOpen, setIsGroupOpen] = useState(props.isOpen);
    const formProviderMethods = useFormContext<FilterFormType>();

    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    const onChangeInStockCheckbox = (name: keyof FilterFormType, value: boolean) => {
        formProviderMethods.setValue(name, !value);
        props.onSubmit(formProviderMethods.getValues());
    };

    return (
        <FilterGroupStyled>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {props.title}
                <FilterGroupArrowStyled iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                <Controller
                    name="onlyInStock"
                    render={({ field }) => (
                        <FilterGroupContentItemStyled isDisabled={props.inStockCount === 0} isActive={field.value}>
                            <Checkbox
                                name={field.name}
                                id={field.name}
                                label={t('In stock')}
                                fieldRef={field}
                                count={props.inStockCount}
                            />
                        </FilterGroupContentItemStyled>
                    )}
                />
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};

export default FilterGroupInStock;
