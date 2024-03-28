import { FilterGroupContent, FilterGroupTitle, FilterGroupWrapper } from './FilterElements';
import { RangeSlider } from 'components/Basic/RangeSlider/RangeSlider';
import { getPriceRounded } from 'helpers/mappers/price';
import { useCurrentFilter } from 'hooks/queryParams/useCurrentFilter';
import { useUpdateFilter } from 'hooks/queryParams/useUpdateFilter';
import { useState } from 'react';

type FilterGroupPriceProps = {
    title: string;
    initialMinPrice: string;
    initialMaxPrice: string;
};

export const FilterGroupPrice: FC<FilterGroupPriceProps> = ({ title, initialMinPrice, initialMaxPrice }) => {
    const [isGroupOpen, setIsGroupOpen] = useState(true);
    const currentFilter = useCurrentFilter();
    const { updateFilterPriceMinimum, updateFilterPriceMaximum } = useUpdateFilter();

    const { minimalPrice, maximalPrice } = currentFilter || {};

    const minPriceOption = getPriceRounded(initialMinPrice);
    const maxPriceOption = getPriceRounded(initialMaxPrice);

    const setMinimalPrice = (value: number) => {
        if (minimalPrice !== value) {
            updateFilterPriceMinimum(minPriceOption === value ? undefined : value);
        }
    };

    const setMaximalPrice = (value: number) => {
        if (maximalPrice !== value) {
            updateFilterPriceMaximum(maxPriceOption === value ? undefined : value);
        }
    };

    return (
        <FilterGroupWrapper>
            <FilterGroupTitle isOpen={isGroupOpen} title={title} onClick={() => setIsGroupOpen(!isGroupOpen)} />
            {isGroupOpen && (
                <FilterGroupContent>
                    <RangeSlider
                        max={maxPriceOption}
                        maxValue={maximalPrice || maxPriceOption}
                        min={minPriceOption}
                        minValue={minimalPrice || minPriceOption}
                        setMaxValueCallback={setMaximalPrice}
                        setMinValueCallback={setMinimalPrice}
                    />
                </FilterGroupContent>
            )}
        </FilterGroupWrapper>
    );
};
