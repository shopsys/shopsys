import { FilterGroupContent, FilterGroupTitle, FilterGroupWrapper } from './FilterElements';
import { FilterGroupIcon } from './FilterGroupIcon';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import useTranslation from 'next-translate/useTranslation';
import { useQueryParams } from 'hooks/useQueryParams';
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
            <FilterGroupTitle onClick={() => setIsGroupOpen((currentGroupVisibility) => !currentGroupVisibility)}>
                {title}
                <FilterGroupIcon isOpen={isGroupOpen} />
            </FilterGroupTitle>
            {isGroupOpen && (
                <FilterGroupContent>
                    <Checkbox
                        name="onlyInStock"
                        id="onlyInStock"
                        onChange={() => updateFilterInStock(!filter?.onlyInStock)}
                        label={t('In stock')}
                        count={inStockCount}
                        value={!!filter?.onlyInStock}
                    />
                </FilterGroupContent>
            )}
        </FilterGroupWrapper>
    );
};
