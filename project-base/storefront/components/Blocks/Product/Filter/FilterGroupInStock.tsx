import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import useTranslation from 'next-translate/useTranslation';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';

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
                aria-label={t('Filter by in stock')}
                count={inStockCount}
                id="onlyInStock"
                label={t('In stock')}
                labelWrapperClassName="text-text-success hover:text-text-success"
                name="onlyInStock"
                value={!!currentFilter?.onlyInStock}
                onChange={() => updateFilterInStockQuery(!currentFilter?.onlyInStock)}
            />
        </div>
    );
};
