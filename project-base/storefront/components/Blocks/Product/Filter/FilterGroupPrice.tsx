import { FilterGroupContent, FilterGroupTitle, FilterGroupWrapper } from './FilterElements';
import { FilterGroupIcon } from './FilterGroupIcon';
import { RangeSlider } from 'components/Basic/RangeSlider/RangeSlider';
import { getPriceRounded } from 'helpers/mappers/price';
import { useQueryParams } from 'hooks/useQueryParams';
import { useState } from 'react';

type FilterGroupPriceProps = {
    title: string;
    initialMinPrice: string;
    initialMaxPrice: string;
};

const TEST_IDENTIFIER = 'blocks-product-filter-filtergroup-price';

export const FilterGroupPrice: FC<FilterGroupPriceProps> = ({ title, initialMinPrice, initialMaxPrice }) => {
    const [isGroupOpen, setIsGroupOpen] = useState(true);
    const { filter, updateFilterPriceMinimum, updateFilterPriceMaximum } = useQueryParams();

    const { minimalPrice, maximalPrice } = filter || {};

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
        <FilterGroupWrapper dataTestId={TEST_IDENTIFIER}>
            <FilterGroupTitle onClick={() => setIsGroupOpen((currentGroupVisibility) => !currentGroupVisibility)}>
                {title}
                <FilterGroupIcon isOpen={isGroupOpen} />
            </FilterGroupTitle>
            {isGroupOpen && (
                <FilterGroupContent>
                    <RangeSlider
                        min={minPriceOption}
                        max={maxPriceOption}
                        minValue={minimalPrice || minPriceOption}
                        maxValue={maximalPrice || maxPriceOption}
                        setMinValueCallback={setMinimalPrice}
                        setMaxValueCallback={setMaximalPrice}
                    />
                </FilterGroupContent>
            )}
        </FilterGroupWrapper>
    );
};
