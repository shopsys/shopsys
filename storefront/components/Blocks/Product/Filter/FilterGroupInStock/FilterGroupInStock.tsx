import { useFilterState } from '../FilterContext/useFilterState';
import {
    FilterGroupArrowStyled,
    FilterGroupContentStyled,
    FilterGroupStyled,
    FilterGroupTitleStyled,
} from 'components/Blocks/Product/Filter/FilterGroup/FilterGroup.style';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { FC, useMemo, useState } from 'react';

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
        <FilterGroupStyled data-testid={TEST_IDENTIFIER}>
            <FilterGroupTitleStyled onClick={() => setIsGroupOpen((currentGroupVisibility) => !currentGroupVisibility)}>
                {title}
                <FilterGroupArrowStyled alt="" iconType="icon" icon="Arrow" isOpen={isGroupOpen} />
            </FilterGroupTitleStyled>
            <FilterGroupContentStyled isOpen={isGroupOpen}>
                <Checkbox
                    name="onlyInStock"
                    id="onlyInStock"
                    onChange={() => dispatch({ type: 'setOnlyInStock', payload: !isOnlyInStock })}
                    label={t('In stock')}
                    count={inStockCount}
                    value={isOnlyInStock}
                />
            </FilterGroupContentStyled>
        </FilterGroupStyled>
    );
};
