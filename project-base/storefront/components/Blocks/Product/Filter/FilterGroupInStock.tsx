import { FilterGroupContent, FilterGroupTitle, FilterGroupWrapper } from './FilterElements';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { useQueryParams } from 'hooks/useQueryParams';
import useTranslation from 'next-translate/useTranslation';
import { useState } from 'react';

type FilterGroupInStockProps = {
    title: string;
    inStockCount: number;
};

const TEST_IDENTIFIER = 'blocks-product-filter-filtergroup-instock';

export const FilterGroupInStock: FC<FilterGroupInStockProps> = ({ title, inStockCount }) => {
    const { t } = useTranslation();
    const [isGroupOpen, setIsGroupOpen] = useState(true);

    const { filter, updateFilterInStock } = useQueryParams();

    return (
        <FilterGroupWrapper dataTestId={TEST_IDENTIFIER}>
            <FilterGroupTitle isOpen={isGroupOpen} title={title} onClick={() => setIsGroupOpen(!isGroupOpen)} />
            {isGroupOpen && (
                <FilterGroupContent>
                    <Checkbox
                        count={inStockCount}
                        id="onlyInStock"
                        label={t('In stock')}
                        name="onlyInStock"
                        value={!!filter?.onlyInStock}
                        onChange={() => updateFilterInStock(!filter?.onlyInStock)}
                    />
                </FilterGroupContent>
            )}
        </FilterGroupWrapper>
    );
};
