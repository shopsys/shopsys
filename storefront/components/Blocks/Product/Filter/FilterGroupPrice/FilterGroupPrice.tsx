import RangeSlider from 'components/Basic/RangeSlider';
import {
    FilterGroupArrowStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import { FC, useCallback, useState } from 'react';
import { useFormContext, useWatch } from 'react-hook-form';
import { useShopsysDispatch, useShopsysSelector } from 'redux/main';
import { optionsFilterActions } from 'redux/slices/optionsFilter';
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
    const parametersFilterState = useShopsysSelector((state) => state.optionsFilter);
    const dispatch = useShopsysDispatch();

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
            setValue('minimalPrice', value);
        },
        [setValue],
    );

    const setMaximalPrice = useCallback(
        (value: number) => {
            setValue('maximalPrice', value);
        },
        [setValue],
    );

    const dispatchMinimalPrice = useCallback(() => {
        if (parametersFilterState.minimalPrice !== null && parametersFilterState.minimalPrice > minimalPrice) {
            dispatch(optionsFilterActions.setMinimalPriceFilter(minimalPrice));
        }
    }, [dispatch, minimalPrice, parametersFilterState.minimalPrice]);

    const dispatchMaximalPrice = useCallback(() => {
        if (parametersFilterState.maximalPrice !== null && parametersFilterState.maximalPrice < maximalPrice) {
            dispatch(optionsFilterActions.setMaximalPriceFilter(minimalPrice));
        }
    }, [dispatch, maximalPrice, minimalPrice, parametersFilterState.maximalPrice]);

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
                    dispatchMinValue={dispatchMinimalPrice}
                    dispatchMaxValue={dispatchMaximalPrice}
                />
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};

export default FilterGroupPrice;
