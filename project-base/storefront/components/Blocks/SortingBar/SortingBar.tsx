import { OVERLAY_PORTAL_ROOT_ID } from 'components/Basic/Portal/portalConstants';
import {
    PRODUCT_LIST_CONTROLS_ELEMENT_ID,
    scrollToProductListControls,
} from 'components/Blocks/Product/Filter/filterElementIds';
import { ProductListViewModeToggle } from 'components/Blocks/Product/ProductsList/ProductListViewModeToggle';
import { ProductsCount } from 'components/Blocks/Product/ProductsList/ProductsCount';
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
import { SortingBarTabs } from './SortingBarTabs';
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

    const sortOptionsShortLabels = {
        ...sortOptionsLabels,
        [TypeProductOrderingModeEnum.PriceAsc]: t('Cheapest'),
        [TypeProductOrderingModeEnum.PriceDesc]: t('Most expensive'),
    };

    const sortOptions = (customSortOptions || DEFAULT_SORT_OPTIONS).filter((sortOption) =>
        !canSeePrices ? !getIsPriceRelatedSortOption(sortOption) : true,
    );
    // With a single option there is nothing to choose, the bar keeps only the product count and the view toggle.
    const hasSortChoice = sortOptions.length > 1;

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
            className="relative flex scroll-mt-fixed-header items-center gap-2 vl:gap-4"
            id={PRODUCT_LIST_CONTROLS_ELEMENT_ID}
        >
            {hasSortChoice && (
                <MobileSortingActions
                    isSortMenuOpen={isSortMenuOpen}
                    selectedSortOption={selectedSortOption}
                    sortOptions={sortOptions}
                    sortOptionsLabels={sortOptionsLabels}
                    sortOptionsShortLabels={sortOptionsShortLabels}
                    onChangeSort={handleChangeSort}
                    onSortMenuClose={() => setIsSortMenuOpen(false)}
                    onSortMenuToggle={() => setIsSortMenuOpen(!isSortMenuOpen)}
                />
            )}

            {portalElement &&
                createPortal(
                    <MobileFilterAction
                        activeFilterCount={activeFilterCount}
                        isFilterPanelOpen={isFilterPanelOpen}
                        onFilterPanelOpen={() => setIsFilterPanelOpen(true)}
                    />,
                    portalElement,
                )}

            {hasSortChoice && (
                <SortingBarTabs
                    selectedSortOption={selectedSortOption}
                    sortOptions={sortOptions}
                    sortOptionsLabels={sortOptionsLabels}
                    onChangeSort={handleChangeSort}
                />
            )}

            <div className="ml-auto flex shrink-0 items-center gap-4">
                {/* Below vl the count is shown next to the page heading, so the bar fits one row. */}
                <ProductsCount className="vl:block hidden" totalCount={totalCount} />

                <ProductListViewModeToggle />
            </div>
        </div>
    );
};
