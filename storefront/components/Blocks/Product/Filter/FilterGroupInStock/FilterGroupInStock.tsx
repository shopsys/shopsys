import {
    FilterGroupArrowStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import { CheckboxControlled } from 'components/Forms/Checkbox/CheckboxControlled';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useState } from 'react';
import { useFormContext } from 'react-hook-form';
import { FilterFormType } from 'types/productFilter';

type FilterGroupInStockProps = {
    title: string;
    isOpen: boolean;
    inStockCount: number;
};

const TEST_IDENTIFIER = 'blocks-product-filter-filtergroup-instock';

export const FilterGroupInStock: FC<FilterGroupInStockProps> = ({ title, isOpen, inStockCount }) => {
    const t = useTypedTranslationFunction();
    const { control } = useFormContext<FilterFormType>();
    const [isGroupOpen, setIsGroupOpen] = useState(isOpen);

    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    return (
        <FilterGroupStyled data-testid={TEST_IDENTIFIER}>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {title}
                <FilterGroupArrowStyled alt="" iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                <CheckboxControlled
                    name="onlyInStock"
                    control={control}
                    formName="filter-form"
                    render={(checkbox, currentValue) => (
                        <FilterGroupContentItemStyled isDisabled={inStockCount === 0} isActive={currentValue}>
                            {checkbox}
                        </FilterGroupContentItemStyled>
                    )}
                    checkboxProps={{
                        label: t('In stock'),
                        count: inStockCount,
                    }}
                />
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};
