import { SortingBarProps } from './SortingBar';
import { FilterIcon } from 'components/Basic/Icon/FilterIcon';
import { SkeletonModuleFilterAndSortingBar } from 'components/Blocks/Skeleton/SkeletonModuleFilterAndSortingBar';
import { Button } from 'components/Forms/Button/Button';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const SortingBar = dynamic(() => import('./SortingBar').then((component) => component.SortingBar), {
    ssr: false,
    loading: () => <SkeletonModuleFilterAndSortingBar />,
});

export const DeferredFilterAndSortingBar: FC<SortingBarProps & { handlePanelOpenerClick?: () => void }> = ({
    handlePanelOpenerClick,
    ...sortingBarProps
}) => {
    const { t } = useTranslation();
    const shouldRender = useDeferredRender('sorting_bar');

    return shouldRender ? (
        <div className="mt-6 flex justify-between items-center gap-2.5 relative vl:border-b vl:border-borderAccentLess">
            {handlePanelOpenerClick && (
                <Button className="flex-1 vl:hidden" variant="inverted" onClick={handlePanelOpenerClick}>
                    <FilterIcon className="size-5" />
                    {t('Filter')}
                </Button>
            )}
            <SortingBar {...sortingBarProps} />
        </div>
    ) : (
        <SkeletonModuleFilterAndSortingBar />
    );
};
