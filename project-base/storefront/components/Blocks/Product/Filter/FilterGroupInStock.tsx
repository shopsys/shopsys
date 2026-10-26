import { Checkbox } from 'components/Forms/Checkbox/Checkbox';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useUpdateFilterQuery } from 'utils/queryParams/useUpdateFilterQuery';

type FilterGroupInStockProps = {
    inStockCount: number;
    idPrefix?: string;
};

export const FilterGroupInStock: FC<FilterGroupInStockProps> = ({ inStockCount, idPrefix = '' }) => {
    const { t } = useTranslation();

    const currentFilter = useCurrentFilterQuery();
    const { updateFilterInStockQuery } = useUpdateFilterQuery();
    const isDisabled = inStockCount === 0 && !currentFilter?.onlyInStock;

    return (
        <div className="rounded-md bg-background-more p-5 py-2.5">
            <Checkbox
                aria-label={t('Filter by in stock', { ns: 'accessibility' })}
                count={inStockCount}
                disabled={isDisabled}
                id={`${idPrefix}onlyInStock`}
                label={t('In stock')}
                labelWrapperClassName={isDisabled ? undefined : 'text-text-success hover:text-text-success'}
                name="onlyInStock"
                value={!!currentFilter?.onlyInStock}
                onChange={() => updateFilterInStockQuery(!currentFilter?.onlyInStock)}
            />
        </div>
    );
};
