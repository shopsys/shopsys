'use client';

import { useCurrentFilterQuery } from 'app/_utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'app/_utils/queryParams/useUpdateFilterQuery';
import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import { useTranslation } from 'components/providers/TranslationProvider';

type FilterGroupInStockProps = {
    inStockCount: number;
};

export const FilterGroupInStock: FC<FilterGroupInStockProps> = ({ inStockCount }) => {
    const { t } = useTranslation();

    const currentFilter = useCurrentFilterQuery();
    const { updateFilterInStockQuery } = useUpdateFilterQuery();

    return (
        <div className="bg-background-more rounded-md p-5 py-2.5">
            <Checkbox
                count={inStockCount}
                id="onlyInStock"
                label={t('In stock')}
                labelWrapperClassName="text-textSuccess hover:text-textSuccess"
                name="onlyInStock"
                value={!!currentFilter?.onlyInStock}
                onChange={() => updateFilterInStockQuery(!currentFilter?.onlyInStock)}
            />
        </div>
    );
};
