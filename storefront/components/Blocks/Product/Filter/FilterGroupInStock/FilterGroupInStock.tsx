import { FC, useState } from 'react';
import {
    FilterGroupArrowStyled,
    FilterGroupContentItemStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import Checkbox from 'components/Forms/Checkbox';
import { Controller } from 'react-hook-form';
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
};

const FilterGroupInStock: FC<FilterGroupInStockProps> = (props) => {
    const t = useTypedTranslationFunction();
    const [isGroupOpen, setIsGroupOpen] = useState(props.isOpen);

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
