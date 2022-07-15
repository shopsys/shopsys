import {
    FilterGroupArrowStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import Checkbox from 'components/Forms/Checkbox';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { FC, useState } from 'react';
import { Controller } from 'react-hook-form';

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
};

const TEST_IDENTIFIER = 'blocks-product-filter-filtergroup-instock';

export const FilterGroupInStock: FC<FilterGroupInStockProps> = ({ title, isOpen, inStockCount }) => {
    const t = useTypedTranslationFunction();
    const [isGroupOpen, setIsGroupOpen] = useState(isOpen);

    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    return (
        <FilterGroupStyled data-testid={TEST_IDENTIFIER}>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {title}
                <FilterGroupArrowStyled iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                <Controller
                    name="onlyInStock"
                    render={({ field }) => (
                        <FilterGroupContentItemStyled isDisabled={inStockCount === 0} isActive={field.value}>
                            <Checkbox
                                name={field.name}
                                id={field.name}
                                label={t('In stock')}
                                fieldRef={field}
                                count={inStockCount}
                            />
                        </FilterGroupContentItemStyled>
                    )}
                />
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};
