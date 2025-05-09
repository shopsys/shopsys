'use client';

import { SortingBar, SortingBarProps } from './SortingBar';
import { FilterIcon } from 'components/Basic/Icon/FilterIcon';
import { Button } from 'components/Forms/Button/Button';
import { useTranslation } from 'components/providers/TranslationProvider';
import { useSessionStore } from 'store/useSessionStore';

export const FilterAndSortingBarWrapper: FC<SortingBarProps> = ({ ...sortingBarProps }) => {
    const { t } = useTranslation();
    const setIsFilterPanelOpen = useSessionStore((s) => s.setIsFilterPanelOpen);

    return (
        <div className="vl:border-b vl:border-borderAccentLess relative flex h-9 flex-col items-center justify-between gap-2.5 sm:flex-row">
            <Button
                className="vl:hidden w-full flex-1 justify-start sm:w-auto"
                variant="secondary"
                onClick={() => setIsFilterPanelOpen(true)}
            >
                <FilterIcon className="size-5" />
                {t('Filter')}
            </Button>

            <SortingBar {...sortingBarProps} />
        </div>
    );
};
