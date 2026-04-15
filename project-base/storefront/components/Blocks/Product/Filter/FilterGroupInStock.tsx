import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import useTranslation from 'utils/i18n/useTranslationWrapper';
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
        <div className="rounded-md bg-background-more p-5 py-2.5">
            <Checkbox
                aria-label={t('Filter by in stock', { ns: 'accessibility' })}
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
