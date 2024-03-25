import { SortingBarItem } from './SortingBarItem';
import { SortIcon } from 'components/Basic/Icon/SortIcon';
import { DEFAULT_SORT } from 'config/constants';
import { ProductOrderingModeEnum } from 'graphql/types';
import { getUrlQueriesWithoutDynamicPageQueries } from 'helpers/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { twMergeCustom } from 'helpers/twMerge';
import { useQueryParams } from 'hooks/useQueryParams';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';

type SortingBarProps = {
    totalCount: number;
    sorting: ProductOrderingModeEnum | null;
    customSortOptions?: ProductOrderingModeEnum[];
};

const DEFAULT_SORT_OPTIONS = [
    ProductOrderingModeEnum.Priority,
    ProductOrderingModeEnum.PriceAsc,
    ProductOrderingModeEnum.PriceDesc,
];

export const SortingBar: FC<SortingBarProps> = ({ sorting, totalCount, customSortOptions, className }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const { sort: sortSelected, updateSort } = useQueryParams();
    const [isSortMenuOpen, setIsSortMenuOpen] = useState(false);

    const asPathWithoutQueryParams = router.asPath.split('?')[0];

    const sortOptionsLabels = {
        [ProductOrderingModeEnum.Priority]: t('priority'),
        [ProductOrderingModeEnum.PriceAsc]: t('price ascending'),
        [ProductOrderingModeEnum.PriceDesc]: t('price descending'),
        [ProductOrderingModeEnum.Relevance]: t('relevance'),
        [ProductOrderingModeEnum.NameAsc]: t('name ascending'),
        [ProductOrderingModeEnum.NameDesc]: t('name descending'),
    };

    const sortOptions = customSortOptions || DEFAULT_SORT_OPTIONS;
    const selectedSortOption = sortSelected || sorting || DEFAULT_SORT;

    return (
        <div
            className={twMergeCustom(
                'relative flex select-none items-center justify-center gap-3 rounded border-greyLighter bg-border p-3 vl:flex-row vl:justify-between vl:rounded-none vl:border-b vl:bg-opacity-0 vl:p-0',
                isSortMenuOpen && 'rounded-b-none',
                className,
            )}
            onClick={() => setIsSortMenuOpen(!isSortMenuOpen)}
        >
            <SortIcon className="w-5 align-middle vl:hidden" />
            <div className="flex flex-col justify-center vl:hidden">
                <div className="font-bold uppercase leading-none">{t('Sort')}</div>
                <div className="text-sm font-bold uppercase leading-none text-primary">
                    {sortOptionsLabels[selectedSortOption]}
                </div>
            </div>

            <div
                className={twJoin(
                    'w-full rounded-b max-vl:bg-border vl:static vl:flex vl:gap-3',
                    isSortMenuOpen ? 'absolute top-full z-[1]' : 'hidden',
                )}
            >
                {sortOptions.map((sortOption) => {
                    // eslint-disable-next-line @typescript-eslint/no-unused-vars
                    const { page, ...queriesWithoutPage } = getUrlQueriesWithoutDynamicPageQueries(router.query);
                    const sortParams = new URLSearchParams({
                        ...queriesWithoutPage,
                        sort: sortOption,
                    }).toString();
                    const sortHref = `${asPathWithoutQueryParams}?${sortParams}`;
                    const isSelectedSortOption = sortOption === selectedSortOption;

                    return (
                        <SortingBarItem
                            key={sortOption}
                            href={sortHref}
                            isActive={isSelectedSortOption}
                            onClick={() => updateSort(sortOption)}
                        >
                            {sortOptionsLabels[sortOption]}
                        </SortingBarItem>
                    );
                })}
            </div>

            <div className="hidden shrink-0 vl:block">
                <strong>{totalCount} </strong>
                {t('Products count', { count: totalCount })}
            </div>
        </div>
    );
};
