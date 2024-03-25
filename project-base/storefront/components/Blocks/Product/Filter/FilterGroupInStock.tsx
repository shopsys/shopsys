import { FilterGroupContent, FilterGroupTitle, FilterGroupWrapper } from './FilterElements';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { useCurrentFilter } from 'hooks/queryParams/useCurrentFilter';
import { useUpdateFilter } from 'hooks/queryParams/useUpdateFilter';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';

type FilterGroupInStockProps = {
    title: string;
    inStockCount: number;
};

export const FilterGroupInStock: FC<FilterGroupInStockProps> = ({ title, inStockCount }) => {
    const { t } = useTranslation();
    const [isGroupOpen, setIsGroupOpen] = useState(true);

    const currentFilter = useCurrentFilter();
    const { updateFilterInStock } = useUpdateFilter();

    return (
        <FilterGroupWrapper>
            <FilterGroupTitle isOpen={isGroupOpen} title={title} onClick={() => setIsGroupOpen(!isGroupOpen)} />
            {isGroupOpen && (
                <FilterGroupContent>
                    <Checkbox
                        count={inStockCount}
                        id="onlyInStock"
                        label={t('In stock')}
                        name="onlyInStock"
                        value={!!currentFilter?.onlyInStock}
                        onChange={() => updateFilterInStock(!currentFilter?.onlyInStock)}
                    />
                </FilterGroupContent>
            )}
        </FilterGroupWrapper>
    );
};
