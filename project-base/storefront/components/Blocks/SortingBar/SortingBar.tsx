import { SortingBarItem } from './SortingBarItem';
import { SortIcon } from 'components/Basic/Icon/SortIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Button } from 'components/Forms/Button/Button';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { DEFAULT_SORT } from 'config/constants';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { useState, useMemo } from 'react';
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
    const { canSeePrices } = useAuthorization();

    const sortOptionsLabels = useMemo(
        () => ({
            [TypeProductOrderingModeEnum.Priority]: t('Priority'),
            [TypeProductOrderingModeEnum.PriceAsc]: t('Price ascending'),
            [TypeProductOrderingModeEnum.PriceDesc]: t('Price descending'),
            [TypeProductOrderingModeEnum.Relevance]: t('Relevance'),
            [TypeProductOrderingModeEnum.NameAsc]: t('Name ascending'),
            [TypeProductOrderingModeEnum.NameDesc]: t('Name descending'),
        }),
        [t],
    );

    const sortOptions = useMemo(
        () =>
            (customSortOptions || DEFAULT_SORT_OPTIONS).filter((sortOption) =>
                !canSeePrices ? !getIsPriceRelatedSortOption(sortOption) : true,
            ),
        [customSortOptions, canSeePrices],
    );

    const selectedSortOption = currentSort || sorting || DEFAULT_SORT;

    const handleChangeSort = (sortOption: TypeProductOrderingModeEnum) => {
        updateSort(sortOption);
        setIsSortMenuOpen(false);
    };

    return (
        <>
            <Button
                aria-controls="sort-dropdown"
                aria-expanded={isSortMenuOpen}
                aria-haspopup="listbox"
                variant="inverted"
                aria-label={t('Sort products by {{ currentSort }}. Click to change sort order.', {
                    currentSort: sortOptionsLabels[selectedSortOption] || t('default order'),
                })}
                className={twJoin(
                    'vl:hidden relative w-full flex-1 justify-start sm:w-auto',
                    isSortMenuOpen && 'z-aboveOverlay',
                )}
                onClick={() => setIsSortMenuOpen(!isSortMenuOpen)}
            >
                <SortIcon className="size-5" />

                <span className="line-clamp-1 overflow-hidden text-left leading-tight">
                    {sortOptionsLabels[selectedSortOption] || t('Sort')}
                </span>
            </Button>

            <div
                aria-label={t('Sort options')}
                id="sort-dropdown"
                role="listbox"
                className={twJoin(
                    'bg-background-default vl:flex vl:flex-row vl:gap-2.5 flex-col rounded-xl',
                    isSortMenuOpen
                        ? 'z-aboveOverlay divide-border-less absolute top-full right-0 mt-1 flex w-[60%] divide-y px-5 py-2.5'
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
                            ariaLabel={
                                isSelectedSortOption
                                    ? t('Sorted by {{ sortOption }}', { sortOption: sortOptionsLabels[sortOption] })
                                    : t('Sort by {{ sortOption }}', { sortOption: sortOptionsLabels[sortOption] })
                            }
                            onClick={() => handleChangeSort(sortOption)}
                        >
                            {sortOptionsLabels[sortOption]}
                        </SortingBarItem>
                    );
                })}
            </div>

            <div className="font-secondary text-input-placeholder-default vl:block hidden text-xs">
                {totalCount} {t('products count', { count: totalCount })}
            </div>

            {isSortMenuOpen && <Overlay isActive={isSortMenuOpen} onClick={() => setIsSortMenuOpen(false)} />}
        </>
    );
};

const getIsPriceRelatedSortOption = (sortOption: TypeProductOrderingModeEnum) =>
    sortOption === TypeProductOrderingModeEnum.PriceAsc || sortOption === TypeProductOrderingModeEnum.PriceDesc;
