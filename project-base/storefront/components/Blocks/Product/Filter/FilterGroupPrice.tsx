import { FilterGroupContent, FilterGroupTitle, FilterGroupWrapper } from './FilterElements';
import { RangeSlider } from 'components/Basic/RangeSlider/RangeSlider';
import { AnimatePresence } from 'framer-motion';
import { useState } from 'react';
import { getPriceRounded } from 'utils/mappers/price';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';

type FilterGroupPriceProps = {
    title: string;
    initialMinPrice: string;
    initialMaxPrice: string;
    isActive: boolean;
};

export const FilterGroupPrice: FC<FilterGroupPriceProps> = ({ title, initialMinPrice, initialMaxPrice, isActive }) => {
    const [isGroupOpen, setIsGroupOpen] = useState(true);
    const currentFilter = useCurrentFilterQuery();
    const { updateFilterPriceMinimumQuery, updateFilterPriceMaximumQuery } = useUpdateFilterQuery();

    const { minimalPrice, maximalPrice } = currentFilter || {};

    const minPriceOption = getPriceRounded(initialMinPrice);
    const maxPriceOption = getPriceRounded(initialMaxPrice);

    const setMinimalPrice = (value: number) => {
        if (minimalPrice !== value) {
            updateFilterPriceMinimumQuery(minPriceOption === value ? undefined : value);
        }
    };

    const setMaximalPrice = (value: number) => {
        if (maximalPrice !== value) {
            updateFilterPriceMaximumQuery(maxPriceOption === value ? undefined : value);
        }
    };

    return (
        <FilterGroupWrapper>
            <FilterGroupTitle
                isActive={isActive}
                isOpen={isGroupOpen}
                title={title}
                onClick={() => setIsGroupOpen(!isGroupOpen)}
            />
            <AnimatePresence initial={false}>
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
            </AnimatePresence>
        </FilterGroupWrapper>
    );
};
