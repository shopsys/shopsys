import { SortingBarProps } from './SortingBar';
import { FilterIcon } from 'components/Basic/Icon/FilterIcon';
import { SkeletonModuleFilterAndSortingBar } from 'components/Blocks/Skeleton/SkeletonModuleFilterAndSortingBar';
import { Button } from 'components/Forms/Button/Button';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { useDeferredRender } from 'utils/useDeferredRender';

const SortingBar = dynamic(() => import('./SortingBar').then((component) => component.SortingBar), {
    ssr: false,
    loading: () => <SkeletonModuleFilterAndSortingBar />,
});

export const DeferredFilterAndSortingBar: FC<SortingBarProps> = ({ ...sortingBarProps }) => {
    const { t } = useTranslation();
    const shouldRender = useDeferredRender('sorting_bar');
    const setIsFilterPanelOpen = useSessionStore((s) => s.setIsFilterPanelOpen);
    const isFilterPanelOpen = useSessionStore((s) => s.isFilterPanelOpen);

    return shouldRender ? (
        <div className="vl:border-b vl:border-border-less relative flex flex-col items-center justify-between gap-2.5 sm:flex-row">
            <Button
                aria-controls="filter-panel"
                aria-expanded={isFilterPanelOpen}
                aria-label={t('Open product filters')}
                className="vl:hidden w-full flex-1 justify-start sm:w-auto"
                variant="secondary"
                onClick={() => setIsFilterPanelOpen(true)}
            >
                <FilterIcon aria-hidden="true" className="size-5" />

                {t('Filter')}
            </Button>

            <SortingBar {...sortingBarProps} />
        </div>
    ) : (
        <SkeletonModuleFilterAndSortingBar />
    );
};
