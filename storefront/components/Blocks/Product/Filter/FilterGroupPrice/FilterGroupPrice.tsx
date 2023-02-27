import { useFilterState } from '../FilterContext/useFilterState';
import { FilterGroupContent, FilterGroupTitle, FilterGroupWrapper } from '../FilterElements';
import { FilterGroupIcon } from '../FilterGroup/FilterGroupIcon';
import { RangeSlider } from 'components/Basic/RangeSlider/RangeSlider';
import { mapPriceForCalculations, roundPrice } from 'helpers/mappers/price';
import { useCallback, useMemo, useState } from 'react';

type FilterGroupPriceProps = {
    title: string;
    isOpen: boolean;
};

const TEST_IDENTIFIER = 'blocks-product-filter-filtergroup-price';

export const FilterGroupPrice: FC<FilterGroupPriceProps> = ({ title, isOpen }) => {
    const [isGroupOpen, setIsGroupOpen] = useState(isOpen);
    const [state, dispatch] = useFilterState();
    const minimalPrice = useMemo(() => state.options.minimalPrice, [state.options.minimalPrice]);
    const maximalPrice = useMemo(() => state.options.maximalPrice, [state.options.maximalPrice]);
    const minimalPriceValue = useMemo(() => state.selected.minimalPrice, [state.selected.minimalPrice]);
    const maximalPriceValue = useMemo(() => state.selected.maximalPrice, [state.selected.maximalPrice]);

    const setMinimalPrice = useCallback(
        (value: number) => {
            if (minimalPriceValue !== value) {
                dispatch({ type: 'setMinimalPrice', payload: value });
            }
        },
        [dispatch, minimalPriceValue],
    );

    const setMaximalPrice = useCallback(
        (value: number) => {
            if (maximalPriceValue !== value) {
                dispatch({ type: 'setMaximalPrice', payload: value });
            }
        },
        [dispatch, maximalPriceValue],
    );

    return (
        <FilterGroupWrapper dataTestId={TEST_IDENTIFIER}>
            <FilterGroupTitle onClick={() => setIsGroupOpen((currentGroupVisibility) => !currentGroupVisibility)}>
                {title}
                <FilterGroupIcon isOpen={isGroupOpen} />
            </FilterGroupTitle>
            <FilterGroupContent isOpen={isGroupOpen}>
                <RangeSlider
                    min={roundPrice(mapPriceForCalculations(minimalPrice))}
                    max={roundPrice(mapPriceForCalculations(maximalPrice))}
                    minValue={minimalPriceValue}
                    maxValue={maximalPriceValue}
                    setMinValueCallback={setMinimalPrice}
                    setMaxValueCallback={setMaximalPrice}
                />
            </FilterGroupContent>
        </FilterGroupWrapper>
    );
};
