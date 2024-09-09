import { SortingBarItem } from './SortingBarItem';
import { SortIcon } from 'components/Basic/Icon/SortIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Button } from 'components/Forms/Button/Button';
import { DEFAULT_SORT } from 'config/constants';
import { useCurrentCustomerData } from 'connectors/customer/CurrentCustomer';
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
    const currentCustomerUser = useCurrentCustomerData();
    const [isSortMenuOpen, setIsSortMenuOpen] = useState(false);

    const sortOptionsLabels = {
        [TypeProductOrderingModeEnum.Priority]: t('Priority'),
        [TypeProductOrderingModeEnum.PriceAsc]: t('Price ascending'),
        [TypeProductOrderingModeEnum.PriceDesc]: t('Price descending'),
        [TypeProductOrderingModeEnum.Relevance]: t('Relevance'),
        [TypeProductOrderingModeEnum.NameAsc]: t('Name ascending'),
        [TypeProductOrderingModeEnum.NameDesc]: t('Name descending'),
    };

    const sortOptions = (customSortOptions || DEFAULT_SORT_OPTIONS).filter((sortOption) =>
        currentCustomerUser?.arePricesHidden ? !getIsPriceRelatedSortOption(sortOption) : true,
    );
    const selectedSortOption = currentSort || sorting || DEFAULT_SORT;

    const handleChangeSort = (sortOption: TypeProductOrderingModeEnum) => {
        updateSort(sortOption);
        setIsSortMenuOpen(false);
    };

    return (
        <>
            <Button
                variant="inverted"
                className={twJoin(
                    'relative w-full flex-1 justify-start sm:w-auto vl:hidden',
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
                    'flex-col rounded-xl bg-background vl:flex vl:flex-row vl:gap-2.5 ',
                    isSortMenuOpen
                        ? 'absolute right-0 top-full z-aboveOverlay mt-1 flex w-[60%] divide-y divide-borderAccentLess px-5 py-2.5'
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
            <div className="hidden font-secondary text-xs text-inputPlaceholder vl:block">
                {totalCount} {t('products count', { count: totalCount })}
            </div>
            {isSortMenuOpen && <Overlay isActive={isSortMenuOpen} onClick={() => setIsSortMenuOpen(false)} />}
        </>
    );
};

const getIsPriceRelatedSortOption = (sortOption: TypeProductOrderingModeEnum) =>
    sortOption === TypeProductOrderingModeEnum.PriceAsc || sortOption === TypeProductOrderingModeEnum.PriceDesc;
