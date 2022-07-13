import RangeSlider from 'components/Basic/RangeSlider';
import {
    FilterGroupArrowStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import { FC, useCallback, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { FilterFormType } from 'types/productFilter';

type FilterGroupPriceProps = {
    /**
     * Group title with arrow
     */
    title: string;
    /**
     * Sets if group is default open
     */
    isOpen: boolean;
    /**
     * Maximal price of price slider
     */
    maximalPrice: number;
    /**
     * Minimal price of price slider
     */
    minimalPrice: number;
};

const TEST_IDENTIFIER = 'blocks-product-filter-filtergroup-price';

const FilterGroupPrice: FC<FilterGroupPriceProps> = ({ title, isOpen, minimalPrice, maximalPrice }) => {
    const [isGroupOpen, setIsGroupOpen] = useState(isOpen);

    const handleGroupClick = () => {
        setIsGroupOpen(!isGroupOpen);
    };

    const { control, setValue } = useFormContext<FilterFormType>();
    const [minimalPriceValue, maximalPriceValue] = useWatch({
        name: ['minimalPrice', 'maximalPrice'],
        control,
    });

    const setMinimalPrice = useCallback(
        (value: number) => {
            if (minimalPriceValue !== value) {
                setValue('minimalPrice', value);
            }
        },
        [minimalPriceValue, setValue],
    );

    const setMaximalPrice = useCallback(
        (value: number) => {
            if (maximalPriceValue !== value) {
                setValue('maximalPrice', value);
            }
        },
        [maximalPriceValue, setValue],
    );

    return (
        <FilterGroupStyled data-testid={TEST_IDENTIFIER}>
            <FilterGroupTitleStyled onClick={handleGroupClick}>
                {title}
                <FilterGroupArrowStyled iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                <RangeSlider
                    min={minimalPrice}
                    max={maximalPrice}
                    minValue={minimalPriceValue}
                    maxValue={maximalPriceValue}
                    setMinValueCallback={setMinimalPrice}
                    setMaxValueCallback={setMaximalPrice}
                />
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};

export default FilterGroupPrice;
