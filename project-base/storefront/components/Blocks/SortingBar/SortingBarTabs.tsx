import { ArrowIcon } from 'components/Basic/Icon/ArrowIcon';
import { TabIndicator } from 'components/Basic/TabIndicator/TabIndicator';
import { TIDs } from 'cypress/tids';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useId, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { twMergeCustom } from 'utils/twMerge';
import useClickClosePopup from 'utils/ui/useClickClosePopup';
import { useKeypress } from 'utils/useKeyPress';
import { type SortOptionsLabels } from './SortingBar';
import { SortingBarOptions } from './SortingBarOptions';
import { getSortingTabClassName, splitSortOptions } from './sortingBarUtils';

type SortingBarTabsProps = {
    selectedSortOption: TypeProductOrderingModeEnum;
    sortOptions: TypeProductOrderingModeEnum[];
    sortOptionsLabels: SortOptionsLabels;
    onChangeSort: (sortOption: TypeProductOrderingModeEnum) => void;
};

export const SortingBarTabs: FC<SortingBarTabsProps> = ({
    selectedSortOption,
    sortOptions,
    sortOptionsLabels,
    onChangeSort,
}) => {
    const { t } = useTranslation();
    const { visibleSortOptions, moreSortOptions } = splitSortOptions(sortOptions, selectedSortOption);
    const tabsRef = useRef<HTMLElement>(null);

    return (
        <nav
            aria-label={t('Sort options', { ns: 'accessibility' })}
            className="relative vl:flex hidden items-center gap-5"
            ref={tabsRef}
        >
            <SortingBarOptions
                selectedSortOption={selectedSortOption}
                sortOptions={visibleSortOptions}
                sortOptionsLabels={sortOptionsLabels}
                variant="tab"
                onChangeSort={onChangeSort}
            />

            <TabIndicator activeKey={selectedSortOption} containerRef={tabsRef} />

            {moreSortOptions.length > 0 && (
                <SortingBarMoreOptions
                    selectedSortOption={selectedSortOption}
                    sortOptions={moreSortOptions}
                    sortOptionsLabels={sortOptionsLabels}
                    onChangeSort={onChangeSort}
                />
            )}
        </nav>
    );
};

type SortingBarMoreOptionsProps = SortingBarTabsProps;

const SortingBarMoreOptions: FC<SortingBarMoreOptionsProps> = ({
    selectedSortOption,
    sortOptions,
    sortOptionsLabels,
    onChangeSort,
}) => {
    const { t } = useTranslation();
    const [isOpen, setIsOpen] = useState(false);
    const listId = useId();
    const wrapperRef = useRef<HTMLDivElement>(null);
    const toggleButtonRef = useRef<HTMLButtonElement>(null);

    useClickClosePopup([wrapperRef], () => setIsOpen(false));

    const closeAndReturnFocus = () => {
        if (!isOpen) {
            return;
        }

        setIsOpen(false);
        toggleButtonRef.current?.focus();
    };

    useKeypress('Escape', closeAndReturnFocus);

    // The chosen option is hidden together with the list, so focus would otherwise fall back to the page.
    const handleChangeSort = (sortOption: TypeProductOrderingModeEnum) => {
        setIsOpen(false);
        onChangeSort(sortOption);
        toggleButtonRef.current?.focus({ preventScroll: true });
    };

    return (
        <div className="relative flex self-stretch" ref={wrapperRef}>
            <button
                aria-controls={listId}
                aria-expanded={isOpen}
                data-tid={TIDs.blocks_sortingbar_more_button}
                className={twMergeCustom(
                    getSortingTabClassName({ isActive: false }),
                    'gap-1 text-link-default hover:text-link-hovered',
                )}
                ref={toggleButtonRef}
                type="button"
                onClick={() => setIsOpen(!isOpen)}
            >
                {t('More')}
                <ArrowIcon aria-hidden className={twJoin('size-3.5 transition-transform', isOpen && 'rotate-180')} />
            </button>

            <div
                id={listId}
                className={twJoin(
                    'absolute top-full left-0 z-above mt-1 min-w-60 flex-col overflow-hidden rounded-input border border-border-less bg-background-default shadow-lg',
                    isOpen ? 'flex motion-safe:animate-dropdown-in' : 'hidden',
                )}
            >
                <SortingBarOptions
                    selectedSortOption={selectedSortOption}
                    sortOptions={sortOptions}
                    sortOptionsLabels={sortOptionsLabels}
                    variant="dropdown"
                    onChangeSort={handleChangeSort}
                />
            </div>
        </div>
    );
};
