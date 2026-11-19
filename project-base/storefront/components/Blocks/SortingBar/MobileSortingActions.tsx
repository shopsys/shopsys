import { CountBadge } from 'components/Basic/CountBadge/CountBadge';
import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { FilterIcon } from 'components/Basic/Icon/FilterIcon';
import { SortIcon } from 'components/Basic/Icon/SortIcon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Button } from 'components/Forms/Button/Button';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { MouseEvent, useEffect, useRef } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useKeypress } from 'utils/useKeyPress';
import { type SortOptionsLabels } from './SortingBar';
import { SortingBarOptions } from './SortingBarOptions';

type MobileSortingActionsProps = {
    isSortMenuOpen: boolean;
    selectedSortOption: TypeProductOrderingModeEnum;
    sortOptions: TypeProductOrderingModeEnum[];
    sortOptionsLabels: SortOptionsLabels;
    sortOptionsShortLabels: SortOptionsLabels;
    onChangeSort: (sortOption: TypeProductOrderingModeEnum) => void;
    onSortMenuClose: () => void;
    onSortMenuToggle: () => void;
};

export const MobileSortingActions: FC<MobileSortingActionsProps> = ({
    isSortMenuOpen,
    selectedSortOption,
    sortOptions,
    sortOptionsLabels,
    sortOptionsShortLabels,
    onChangeSort,
    onSortMenuClose,
    onSortMenuToggle,
}) => {
    const { t } = useTranslation();
    const sortButtonRef = useRef<HTMLButtonElement | null>(null);
    const sortDropdownRef = useRef<HTMLElement>(null);
    // The button shows a short label (e.g. "Cheapest"), so a long translation does not stretch it; the list shows the full one.
    const selectedSortOptionShortLabel = sortOptionsShortLabels[selectedSortOption];

    useEffect(() => {
        if (isSortMenuOpen) {
            sortDropdownRef.current?.querySelector<HTMLAnchorElement>('[aria-current="true"]')?.focus();
        }
    }, [isSortMenuOpen]);

    const closeSortMenuAndReturnFocus = () => {
        if (isSortMenuOpen) {
            onSortMenuClose();
            sortButtonRef.current?.focus();
        }
    };

    useKeypress('Escape', closeSortMenuAndReturnFocus);

    const handleSortButtonClick = (event: MouseEvent<HTMLButtonElement>) => {
        sortButtonRef.current = event.currentTarget;
        onSortMenuToggle();
    };

    return (
        <>
            <div
                className={twJoin('relative vl:hidden min-w-0 flex-1 sm:max-w-80', isSortMenuOpen && 'z-aboveOverlay')}
            >
                <Button
                    aria-controls="sort-dropdown"
                    aria-expanded={isSortMenuOpen}
                    variant="secondary"
                    aria-label={t('Sort products by {{ currentSort }}. Click to change sort order.', {
                        ns: 'accessibility',
                        currentSort: sortOptionsLabels[selectedSortOption] || t('default order'),
                    })}
                    className={twJoin(
                        'w-full justify-between',
                        isSortMenuOpen &&
                            'bg-button-secondary-bg-active text-button-secondary-text-active outline-button-secondary-border-active',
                    )}
                    size="large"
                    title={t('Sort')}
                    onClick={handleSortButtonClick}
                >
                    <span className="flex min-w-0 items-center gap-2">
                        <SortIcon aria-hidden="true" className="size-5 shrink-0" />

                        <span className="truncate text-left">
                            <span className="font-normal">{t('Sort')}:</span>{' '}
                            <span className="font-bold">{selectedSortOptionShortLabel}</span>
                        </span>
                    </span>

                    <ArrowIcon
                        aria-hidden
                        className={twJoin('size-5 shrink-0 transition-transform', isSortMenuOpen && 'rotate-180')}
                    />
                </Button>

                <nav
                    aria-label={t('Sort options', { ns: 'accessibility' })}
                    id="sort-dropdown"
                    ref={sortDropdownRef}
                    className={twJoin(
                        'absolute top-full left-0 mt-1 w-full flex-col divide-y divide-border-less overflow-hidden rounded-input border border-border-less bg-background-default shadow-lg',
                        isSortMenuOpen ? 'flex' : 'hidden',
                    )}
                >
                    <SortingBarOptions
                        variant="dropdown"
                        selectedSortOption={selectedSortOption}
                        sortOptions={sortOptions}
                        sortOptionsLabels={sortOptionsLabels}
                        onChangeSort={onChangeSort}
                    />
                </nav>
            </div>

            <Overlay isActive={isSortMenuOpen} onClick={onSortMenuClose} />
        </>
    );
};

type MobileFilterActionProps = {
    activeFilterCount: number;
    isFilterPanelOpen: boolean;
    onFilterPanelOpen: () => void;
};

export const MobileFilterAction: FC<MobileFilterActionProps> = ({
    activeFilterCount,
    isFilterPanelOpen,
    onFilterPanelOpen,
}) => {
    const { t } = useTranslation();

    return (
        <Button
            aria-controls="filter-panel"
            aria-expanded={isFilterPanelOpen}
            aria-label={t('Open product filters', { ns: 'accessibility' })}
            className="fixed right-4 bottom-[calc(5rem+env(safe-area-inset-bottom))] z-floatingAction flex vl:hidden min-h-12 items-center gap-1 rounded-full shadow-[0_8px_24px_rgba(0,0,0,0.24)]"
            size="large"
            title={t('Filter')}
            variant="secondary"
            onClick={onFilterPanelOpen}
        >
            <FilterIcon aria-hidden="true" className="size-5 shrink-0" />

            <span className="font-secondary font-semibold text-sm">{t('Filter')}</span>

            {activeFilterCount > 0 && (
                <CountBadge
                    aria-hidden="true"
                    className="absolute -top-0.5 -right-0.5 bg-background-warning text-text-default"
                >
                    {activeFilterCount}
                </CountBadge>
            )}
        </Button>
    );
};
