import { SortingBarProps } from './SortingBar';
import { FilterIcon } from 'components/Basic/Icon/FilterIcon';
import { SkeletonModuleFilterAndSortingBar } from 'components/Blocks/Skeleton/SkeletonModuleFilterAndSortingBar';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const SortingBar = dynamic(() => import('./SortingBar').then((component) => component.SortingBar), {
    ssr: false,
    loading: () => <SkeletonModuleFilterAndSortingBar />,
});

export const DeferredFilterAndSortingBar: FC<SortingBarProps & { handlePanelOpenerClick: () => void }> = ({
    handlePanelOpenerClick,
    ...sortingBarProps
}) => {
    const { t } = useTranslation();
    const shouldRender = useDeferredRender('sorting_bar');

    return shouldRender ? (
        <div className="mt-6 flex flex-col items-stretch gap-3 sm:flex-row h-28 sm:h-12 vl:h-9">
            <div
                className="relative flex flex-1 cursor-pointer items-center justify-center rounded bg-primary p-3 font-bold uppercase text-white vl:mb-3 vl:hidden"
                onClick={handlePanelOpenerClick}
            >
                <FilterIcon className="mr-3 w-6 font-bold text-white" />
                {t('Filter')}
            </div>

            <SortingBar className="flex-1" {...sortingBarProps} />
        </div>
    ) : (
        <SkeletonModuleFilterAndSortingBar />
    );
};
