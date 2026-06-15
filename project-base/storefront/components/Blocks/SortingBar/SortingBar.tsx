import { OVERLAY_PORTAL_ROOT_ID } from 'components/Basic/Portal/Portal';
import { scrollToSelectedFilters } from 'components/Blocks/Product/Filter/filterElementIds';
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
import { MobileSortingActions } from './MobileSortingActions';
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
        scrollToSelectedFilters();
    };

    return (
        <div className="vl:relative vl:flex vl:flex-row vl:items-center vl:justify-between vl:gap-2.5 vl:border-border-less vl:border-b">
            {portalElement &&
                createPortal(
                    <MobileSortingActions
                        activeFilterCount={activeFilterCount}
                        isFilterPanelOpen={isFilterPanelOpen}
                        isSortMenuOpen={isSortMenuOpen}
                        selectedSortOption={selectedSortOption}
                        sortOptions={sortOptions}
                        sortOptionsLabels={sortOptionsLabels}
                        onChangeSort={handleChangeSort}
                        onFilterPanelOpen={() => setIsFilterPanelOpen(true)}
                        onSortMenuClose={() => setIsSortMenuOpen(false)}
                        onSortMenuToggle={() => setIsSortMenuOpen(!isSortMenuOpen)}
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

            <div className="vl:block hidden font-secondary text-input-placeholder-default text-xs">
                {totalCount} {t('products count', { count: totalCount })}
            </div>
        </div>
    );
};
