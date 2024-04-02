import { FilterGroupContent, FilterGroupTitle, FilterGroupWrapper } from './FilterElements';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';

type FilterGroupInStockProps = {
    title: string;
    inStockCount: number;
};

export const FilterGroupInStock: FC<FilterGroupInStockProps> = ({ title, inStockCount }) => {
    const { t } = useTranslation();
    const [isGroupOpen, setIsGroupOpen] = useState(true);

    const currentFilter = useCurrentFilterQuery();
    const { updateFilterInStockQuery } = useUpdateFilterQuery();

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
                        onChange={() => updateFilterInStockQuery(!currentFilter?.onlyInStock)}
                    />
                </FilterGroupContent>
            )}
        </FilterGroupWrapper>
    );
};
