import { RangeSlider } from 'components/Basic/RangeSlider/RangeSlider';
import { AnimatePresence } from 'framer-motion';
import { useState } from 'react';
import { createAriaParameter } from 'utils/accessibility/createAriaParameter';
import { getPriceRounded } from 'utils/mappers/price';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';
import { FilterGroupContent, FilterGroupTitle, FilterGroupWrapper } from './FilterElements';

type FilterGroupPriceProps = {
    title: string;
    ariaLabel: string;
    initialMinPrice: string;
    initialMaxPrice: string;
    isActive: boolean;
    isStandalone?: boolean;
    idPrefix?: string;
};

export const FilterGroupPrice: FC<FilterGroupPriceProps> = ({
    title,
    ariaLabel,
    initialMinPrice,
    initialMaxPrice,
    isActive,
    isStandalone = false,
    idPrefix = '',
}) => {
    const [isGroupOpen, setIsGroupOpen] = useState(true);
    const currentFilter = useCurrentFilterQuery();
    const { updateFilterPriceMinimumQuery, updateFilterPriceMaximumQuery } = useUpdateFilterQuery();

    const { minimalPrice, maximalPrice } = currentFilter || {};

    const minPriceOption = getPriceRounded(initialMinPrice);
    const maxPriceOption = getPriceRounded(initialMaxPrice);
    const contentId = createAriaParameter(`${idPrefix}filter-group`, title);

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
        <FilterGroupWrapper isStandalone={isStandalone}>
            {!isStandalone && (
                <FilterGroupTitle
                    ariaLabel={ariaLabel}
                    contentId={contentId}
                    isActive={isActive}
                    isOpen={isGroupOpen}
                    title={title}
                    onClick={() => setIsGroupOpen(!isGroupOpen)}
                />
            )}
            <AnimatePresence initial={false}>
                {(isStandalone || isGroupOpen) && (
                    <FilterGroupContent id={contentId} isStandalone={isStandalone}>
                        <RangeSlider
                            isDisabled={minPriceOption === maxPriceOption && !isActive}
                            idPrefix={idPrefix}
                            max={maxPriceOption}
                            maxValue={maximalPrice || maxPriceOption}
                            min={minPriceOption}
                            minValue={minimalPrice || minPriceOption}
                            setMaxValueCallback={setMaximalPrice}
                            setMinValueCallback={setMinimalPrice}
                            title={title}
                        />
                    </FilterGroupContent>
                )}
            </AnimatePresence>
        </FilterGroupWrapper>
    );
};
