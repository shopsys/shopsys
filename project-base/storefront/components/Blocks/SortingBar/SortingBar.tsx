import { SortIcon } from 'components/Basic/Icon/IconsSvg';
import { ProductOrderingModeEnumApi } from 'graphql/generated';
import { DEFAULT_SORT } from 'helpers/filterOptions/seoCategories';
import { getUrlQueriesWithoutDynamicPageQueries } from 'helpers/parsing/urlParsing';
import useTranslation from 'next-translate/useTranslation';
import { useQueryParams } from 'hooks/useQueryParams';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { SortingBarItem } from './SortingBarItem';
import { twMergeCustom } from 'helpers/twMerge';

type SortingBarProps = {
    totalCount: number;
    sorting: ProductOrderingModeEnumApi | null;
    customSortOptions?: ProductOrderingModeEnumApi[];
};

const DEFAULT_SORT_OPTIONS = [
    ProductOrderingModeEnumApi.PriorityApi,
    ProductOrderingModeEnumApi.PriceAscApi,
    ProductOrderingModeEnumApi.PriceDescApi,
];

export const SortingBar: FC<SortingBarProps> = ({ sorting, totalCount, customSortOptions, className }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const { sort: sortSelected, updateSort } = useQueryParams();
    const [isSortMenuOpen, setIsSortMenuOpen] = useState(false);

    const asPathWithoutQueryParams = router.asPath.split('?')[0];

    const sortOptionsLabels = {
        [ProductOrderingModeEnumApi.PriorityApi]: t('priority'),
        [ProductOrderingModeEnumApi.PriceAscApi]: t('price ascending'),
        [ProductOrderingModeEnumApi.PriceDescApi]: t('price descending'),
        [ProductOrderingModeEnumApi.RelevanceApi]: t('relevance'),
        [ProductOrderingModeEnumApi.NameAscApi]: t('name ascending'),
        [ProductOrderingModeEnumApi.NameDescApi]: t('name descending'),
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
                            isActive={isSelectedSortOption}
                            onClick={() => updateSort(sortOption)}
                            href={sortHref}
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
