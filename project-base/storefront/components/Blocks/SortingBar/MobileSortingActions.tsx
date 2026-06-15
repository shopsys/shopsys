import { FilterIcon } from 'components/Basic/Icon/FilterIcon';
import { SortIcon } from 'components/Basic/Icon/SortIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Button } from 'components/Forms/Button/Button';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { type SortOptionsLabels } from './SortingBar';
import { SortingBarOptions } from './SortingBarOptions';

type MobileSortingActionsProps = {
    activeFilterCount: number;
    isFilterPanelOpen: boolean;
    isSortMenuOpen: boolean;
    selectedSortOption: TypeProductOrderingModeEnum;
    sortOptions: TypeProductOrderingModeEnum[];
    sortOptionsLabels: SortOptionsLabels;
    onChangeSort: (sortOption: TypeProductOrderingModeEnum) => void;
    onFilterPanelOpen: () => void;
    onSortMenuClose: () => void;
    onSortMenuToggle: () => void;
};

export const MobileSortingActions: FC<MobileSortingActionsProps> = ({
    activeFilterCount,
    isFilterPanelOpen,
    isSortMenuOpen,
    selectedSortOption,
    sortOptions,
    sortOptionsLabels,
    onChangeSort,
    onFilterPanelOpen,
    onSortMenuClose,
    onSortMenuToggle,
}) => {
    const { t } = useTranslation();

    return (
        <>
            <Button
                aria-controls="sort-dropdown"
                aria-expanded={isSortMenuOpen}
                aria-haspopup="menu"
                variant="inverted"
                aria-label={t('Sort products by {{ currentSort }}. Click to change sort order.', {
                    ns: 'accessibility',
                    currentSort: sortOptionsLabels[selectedSortOption] || t('default order'),
                })}
                className={twJoin(
                    'fixed bottom-[calc(5rem+env(safe-area-inset-bottom))] left-4 z-floatingAction vl:hidden size-14 rounded-full p-0 shadow-[0_8px_24px_rgba(0,0,0,0.24)]',
                    isSortMenuOpen && 'z-aboveOverlay',
                )}
                title={t('Sort')}
                onClick={onSortMenuToggle}
            >
                <SortIcon aria-hidden="true" className="size-6" />

                <span className="sr-only">{sortOptionsLabels[selectedSortOption] || t('Sort')}</span>
            </Button>

            <div
                aria-label={t('Sort options', { ns: 'accessibility' })}
                id="sort-dropdown"
                role="menu"
                className={twJoin(
                    'fixed bottom-[calc(9rem+env(safe-area-inset-bottom))] left-4 z-aboveOverlay vl:hidden w-[min(20rem,calc(100vw-2rem))] flex-col divide-y divide-border-less rounded-xl bg-background-default px-5 py-2.5 shadow-[0_12px_32px_rgba(0,0,0,0.24)]',
                    isSortMenuOpen ? 'flex' : 'hidden',
                )}
            >
                <SortingBarOptions
                    itemRole="menuitem"
                    selectedSortOption={selectedSortOption}
                    sortOptions={sortOptions}
                    sortOptionsLabels={sortOptionsLabels}
                    onChangeSort={onChangeSort}
                />
            </div>

            <Button
                aria-controls="filter-panel"
                aria-expanded={isFilterPanelOpen}
                aria-label={t('Open product filters', { ns: 'accessibility' })}
                className="fixed right-4 bottom-[calc(5rem+env(safe-area-inset-bottom))] z-floatingAction vl:hidden size-14 rounded-full p-0 shadow-[0_8px_24px_rgba(0,0,0,0.24)]"
                title={t('Filter')}
                variant="secondary"
                onClick={onFilterPanelOpen}
            >
                <FilterIcon aria-hidden="true" className="size-6" />

                {activeFilterCount > 0 && (
                    <span
                        aria-hidden="true"
                        className="absolute top-2 right-2 flex h-5 min-w-5 items-center justify-center rounded-full bg-background-accent px-1 font-bold font-secondary text-text-inverted text-xs leading-normal"
                    >
                        {activeFilterCount}
                    </span>
                )}

                <span className="sr-only">{t('Filter')}</span>
            </Button>

            {isSortMenuOpen && <Overlay isActive={isSortMenuOpen} onClick={onSortMenuClose} />}
        </>
    );
};
