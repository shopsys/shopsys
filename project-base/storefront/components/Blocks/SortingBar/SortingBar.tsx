import { SortingBarItem } from './SortingBarItem';
import { SortIcon } from 'components/Basic/Icon/SortIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Button } from 'components/Forms/Button/Button';
import { DEFAULT_SORT } from 'config/constants';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { getUrlQueriesWithoutDynamicPageQueries } from 'utils/parsing/getUrlQueriesWithoutDynamicPageQueries';
import { useCurrentSortQuery } from 'utils/queryParams/useCurrentSortQuery';
import { useUpdateSortQuery } from 'utils/queryParams/useUpdateSortQuery';

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

export const SortingBar: FC<SortingBarProps> = ({ sorting, totalCount, customSortOptions }) => {
    const { t } = useTranslation();
    const router = useRouter();
    const asPathWithoutQueryParams = router.asPath.split('?')[0];
    const currentSort = useCurrentSortQuery();
    const updateSort = useUpdateSortQuery();
    const [isSortMenuOpen, setIsSortMenuOpen] = useState(false);

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

    const handleChangeSort = (sortOption: TypeProductOrderingModeEnum) => {
        updateSort(sortOption);
        setIsSortMenuOpen(false);
    };

    return (
        <>
            <Button
                className={twJoin('relative flex-1 vl:mb-3 vl:hidden gap-3', isSortMenuOpen && 'z-aboveOverlay')}
                variant="inverted"
                onClick={() => setIsSortMenuOpen(!isSortMenuOpen)}
            >
                <SortIcon className="size-5" />
                {t('Sort')}
            </Button>
            <div
                className={twJoin(
                    'bg-background rounded-xl vl:flex flex-col vl:flex-row vl:gap-2.5 ',
                    isSortMenuOpen
                        ? 'flex absolute w-[60%] right-0 top-full z-aboveOverlay mt-1 py-2.5 px-5 divide-y divide-borderAccentLess'
                        : 'hidden',
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
                            onClick={() => handleChangeSort(sortOption)}
                        >
                            {sortOptionsLabels[sortOption]}
                        </SortingBarItem>
                    );
                })}
            </div>
            <div className="text-inputPlaceholder text-xs hidden vl:block">
                {totalCount} {t('Products count', { count: totalCount })}
            </div>
            {isSortMenuOpen && <Overlay isActive={isSortMenuOpen} onClick={() => setIsSortMenuOpen(false)} />}
        </>
    );
};
