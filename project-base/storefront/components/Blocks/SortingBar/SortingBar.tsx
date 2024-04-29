import { SortingBarItem } from './SortingBarItem';
import { SortIcon } from 'components/Basic/Icon/SortIcon';
import { DEFAULT_SORT } from 'config/constants';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { getUrlQueriesWithoutDynamicPageQueries } from 'utils/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { useCurrentSortQuery } from 'utils/queryParams/useCurrentSortQuery';
import { useUpdateSortQuery } from 'utils/queryParams/useUpdateSortQuery';
import { twMergeCustom } from 'utils/twMerge';

export type SortingBarProps = {
    totalCount: number;
    sorting: TypeProductOrderingModeEnum | null;
    customSortOptions?: TypeProductOrderingModeEnum[];
};

const DEFAULT_SORT_OPTIONS = [
    TypeProductOrderingModeEnum.Priority,
    TypeProductOrderingModeEnum.PriceAsc,
    TypeProductOrderingModeEnum.PriceDesc,
];

export const SortingBar: FC<SortingBarProps> = ({ sorting, totalCount, customSortOptions, className }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const currentSort = useCurrentSortQuery();
    const updateSort = useUpdateSortQuery();
    const [isSortMenuOpen, setIsSortMenuOpen] = useState(false);

    const asPathWithoutQueryParams = router.asPath.split('?')[0];

    const sortOptionsLabels = {
        [TypeProductOrderingModeEnum.Priority]: t('priority'),
        [TypeProductOrderingModeEnum.PriceAsc]: t('price ascending'),
        [TypeProductOrderingModeEnum.PriceDesc]: t('price descending'),
        [TypeProductOrderingModeEnum.Relevance]: t('relevance'),
        [TypeProductOrderingModeEnum.NameAsc]: t('name ascending'),
        [TypeProductOrderingModeEnum.NameDesc]: t('name descending'),
    };

    const sortOptions = customSortOptions || DEFAULT_SORT_OPTIONS;
    const selectedSortOption = currentSort || sorting || DEFAULT_SORT;

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
