import { useFilterState } from '../FilterContext/useFilterState';
import { FilterGroupContent, FilterGroupTitle, FilterGroupWrapper } from '../FilterElements';
import { FilterGroupIcon } from '../FilterGroup/FilterGroupIcon';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useMemo, useState } from 'react';

type FilterGroupInStockProps = {
    title: string;
    isOpen: boolean;
    inStockCount: number;
};

const TEST_IDENTIFIER = 'blocks-product-filter-filtergroup-instock';

export const FilterGroupInStock: FC<FilterGroupInStockProps> = ({ title, isOpen }) => {
    const t = useTypedTranslationFunction();
    const [isGroupOpen, setIsGroupOpen] = useState(isOpen);
    const [state, dispatch] = useFilterState();
    const isOnlyInStock = useMemo(() => state.selected.onlyInStock, [state.selected.onlyInStock]);
    const inStockCount = useMemo(() => state.options.inStock, [state.options.inStock]);

    return (
        <FilterGroupWrapper dataTestId={TEST_IDENTIFIER}>
            <FilterGroupTitle onClick={() => setIsGroupOpen((currentGroupVisibility) => !currentGroupVisibility)}>
                {title}
                <FilterGroupIcon isOpen={isGroupOpen} />
            </FilterGroupTitle>
            <FilterGroupContent isOpen={isGroupOpen}>
                <Checkbox
                    name="onlyInStock"
                    id="onlyInStock"
                    onChange={() => dispatch({ type: 'setOnlyInStock', payload: !isOnlyInStock })}
                    label={t('In stock')}
                    count={inStockCount}
                    value={isOnlyInStock}
                />
            </FilterGroupContent>
        </FilterGroupWrapper>
    );
};
