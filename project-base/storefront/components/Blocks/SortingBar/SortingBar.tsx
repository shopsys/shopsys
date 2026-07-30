import { OVERLAY_PORTAL_ROOT_ID } from 'components/Basic/Portal/Portal';
import {
    PRODUCT_LIST_CONTROLS_ELEMENT_ID,
    scrollToProductListControls,
} from 'components/Blocks/Product/Filter/filterElementIds';
import { ProductListViewModeToggle } from 'components/Blocks/Product/ProductsList/ProductListViewModeToggle';
import { useAuthorization } from 'components/providers/AuthorizationProvider';
import { DEFAULT_SORT } from 'config/constants';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useEffect, useState } from 'react';
import { createPortal } from 'react-dom';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useCurrentFilterQuery } from 'utils/queryParams/useCurrentFilterQuery';
import { useCurrentSortQuery } from 'utils/queryParams/useCurrentSortQuery';
import { useUpdateSortQuery } from 'utils/queryParams/useUpdateSortQuery';
import { MobileFilterAction, MobileSortingActions } from './MobileSortingActions';
import { SortingBarOptions } from './SortingBarOptions';
import { getActiveFilterCount, getIsPriceRelatedSortOption } from './sortingBarUtils';

export type SortingBarProps = {
    totalCount: number;
    sorting: TypeProductOrderingModeEnum | null;
    customSortOptions?: TypeProductOrderingModeEnum[];
};

export type SortOptionsLabels = Record<TypeProductOrderingModeEnum, string>;

const DEFAULT_SORT_OPTIONS = [
    TypeProductOrderingModeEnum.Priority,
    TypeProductOrderingModeEnum.PriceAsc,
    TypeProductOrderingModeEnum.PriceDesc,
];

export const SortingBar: FC<SortingBarProps> = ({ sorting, totalCount, customSortOptions }) => {
    const { t } = useTranslation();
    const currentSort = useCurrentSortQuery();
    const updateSort = useUpdateSortQuery();
    const [isSortMenuOpen, setIsSortMenuOpen] = useState(false);
    const { canSeePrices } = useAuthorization();
    const setIsFilterPanelOpen = useSessionStore((s) => s.setIsFilterPanelOpen);
    const isFilterPanelOpen = useSessionStore((s) => s.isFilterPanelOpen);
    const defaultProductFiltersMap = useSessionStore((s) => s.defaultProductFiltersMap);
    const currentFilter = useCurrentFilterQuery();
    const activeFilterCount = getActiveFilterCount(currentFilter, defaultProductFiltersMap);

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

    const selectedSortOption = currentSort || sorting || DEFAULT_SORT;
    const [portalElement, setPortalElement] = useState<HTMLElement | null>(null);

    useEffect(() => {
        setPortalElement(document.getElementById(OVERLAY_PORTAL_ROOT_ID) ?? document.body);
    }, []);

    useEffect(() => {
        if (isFilterPanelOpen) {
            setIsSortMenuOpen(false);
        }
    }, [isFilterPanelOpen]);

    const handleChangeSort = (sortOption: TypeProductOrderingModeEnum) => {
        updateSort(sortOption);
        setIsSortMenuOpen(false);
        scrollToProductListControls();
    };

    return (
        <div
            className="relative flex scroll-mt-fixed-header flex-col gap-2.5 vl:border-border-less vl:border-b sm:flex-row sm:items-center sm:justify-between"
            id={PRODUCT_LIST_CONTROLS_ELEMENT_ID}
        >
            <MobileSortingActions
                isSortMenuOpen={isSortMenuOpen}
                selectedSortOption={selectedSortOption}
                sortOptions={sortOptions}
                sortOptionsLabels={sortOptionsLabels}
                onChangeSort={handleChangeSort}
                onSortMenuClose={() => setIsSortMenuOpen(false)}
                onSortMenuToggle={() => setIsSortMenuOpen(!isSortMenuOpen)}
            />

            {portalElement &&
                createPortal(
                    <MobileFilterAction
                        activeFilterCount={activeFilterCount}
                        isFilterPanelOpen={isFilterPanelOpen}
                        onFilterPanelOpen={() => setIsFilterPanelOpen(true)}
                    />,
                    portalElement,
                )}

            <div
                aria-label={t('Sort options', { ns: 'accessibility' })}
                role="listbox"
                className="vl:flex hidden vl:flex-row vl:gap-2.5 rounded-xl bg-background-default"
            >
                <SortingBarOptions
                    itemRole="option"
                    selectedSortOption={selectedSortOption}
                    sortOptions={sortOptions}
                    sortOptionsLabels={sortOptionsLabels}
                    onChangeSort={handleChangeSort}
                />
            </div>

            <div className="flex w-full items-center justify-between gap-2 sm:w-auto md:ml-auto">
                <div className="font-secondary text-input-placeholder-default text-xs">
                    {totalCount} {t('products count', { count: totalCount })}
                </div>

                <ProductListViewModeToggle />
            </div>
        </div>
    );
};
