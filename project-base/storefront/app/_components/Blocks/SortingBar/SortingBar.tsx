'use client';

import { SortingBarItem } from './SortingBarItem';
import { SortIcon } from 'components/Basic/Icon/SortIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Button } from 'components/Forms/Button/Button';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { useTranslation } from 'components/providers/TranslationProvider';
import { DEFAULT_SORT } from 'config/constants';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { usePathname, useRouter, useSearchParams } from 'next/navigation';
import { useState } from 'react';
import { twJoin } from 'tailwind-merge';

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
    const searchParams = useSearchParams();
    const pathname = usePathname();

    // const asPathWithoutQueryParams = router.asPath.split('?')[0];
    // const currentSort = useCurrentSortQuery();

    // const [searchParams, setSearchParams] = useSearchParams();

    // const updateSort = useUpdateSortQuery();

    const [isSortMenuOpen, setIsSortMenuOpen] = useState(false);
    const { canSeePrices } = useAuthorization();

    const sortOptionsLabels = {
        [TypeProductOrderingModeEnum.Priority]: t('Priority'),
        [TypeProductOrderingModeEnum.PriceAsc]: t('Price ascending'),
        [TypeProductOrderingModeEnum.PriceDesc]: t('Price descending'),
        [TypeProductOrderingModeEnum.Relevance]: t('Relevance'),
        [TypeProductOrderingModeEnum.NameAsc]: t('Name ascending'),
        [TypeProductOrderingModeEnum.NameDesc]: t('Name descending'),
    };

    const sortOptions = (customSortOptions || DEFAULT_SORT_OPTIONS).filter((sortOption) =>
        !canSeePrices ? !getIsPriceRelatedSortOption(sortOption) : true,
    );

    const selectedSortOption = sorting || DEFAULT_SORT;

    const handleChangeSort = (sortOption: TypeProductOrderingModeEnum) => {
        const newSearchParams = new URLSearchParams(searchParams?.toString());

        if (sortOption === sortOptions[0]) {
            newSearchParams.delete('sort');
        } else {
            newSearchParams.set('sort', sortOption);
        }

        const newUrl = newSearchParams.toString() ? `?${newSearchParams.toString()}` : pathname!;

        router.push(newUrl, { scroll: false });

        setIsSortMenuOpen(false);
    };

    return (
        <>
            <Button
                variant="inverted"
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
                className={twJoin(
                    'bg-background-default vl:flex vl:flex-row vl:gap-2.5 flex-col rounded-xl',
                    isSortMenuOpen
                        ? 'z-aboveOverlay divide-border-less absolute top-full right-0 mt-1 flex w-[60%] divide-y px-5 py-2.5'
                        : 'hidden',
                )}
            >
                {sortOptions.map((sortOption) => {
                    // const { page, ...queriesWithoutPage } = getUrlQueriesWithoutDynamicPageQueries(router.query);
                    // const sortParams = new URLSearchParams({
                    //     ...queriesWithoutPage,
                    //     sort: sortOption,
                    // }).toString();

                    // const params = new URLSearchParams(searchParams.toString())
                    // params.set(name, value)

                    // const sortHref = `${pathname}?${sortOption}`;
                    const isSelectedSortOption = sortOption === selectedSortOption;

                    return (
                        <SortingBarItem
                            key={sortOption}
                            // href={sortHref}
                            isActive={isSelectedSortOption}
                            onClick={() => handleChangeSort(sortOption)}
                        >
                            {sortOptionsLabels[sortOption]}
                        </SortingBarItem>
                        // <button
                        //     key={sortOption}
                        //     className={twMergeCustom(
                        //         'font-secondary text-link-default hover:text-linkHovered vl:relative vl:rounded-t-xl vl:bg-background-more vl:px-5 vl:py-2.5 vl:text-center h-9 py-4 text-right text-xs font-bold uppercase underline',
                        //         sortOption === selectedSortOption &&
                        //             'text-text-default vl:border vl:border-border-less vl:bg-background-default vl:after:absolute vl:after:bottom-[-2px] vl:after:left-0 vl:after:h-[2px] vl:after:w-full vl:after:bg-background-default font-semibold no-underline',
                        //     )}
                        //     onClick={() => handleChangeSort(sortOption)}
                        // >
                        //     {sortOptionsLabels[sortOption]}
                        // </button>
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
