import { SortingBarProps } from './SortingBar';
import { FilterIcon } from 'components/Basic/Icon/FilterIcon';
import { LabelLink } from 'components/Basic/LabelLink/LabelLink';
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
            <LabelLink
                className="relative flex-1 font-bold uppercase vl:mb-3 vl:hidden gap-3"
                onClick={handlePanelOpenerClick}
            >
                <FilterIcon className="w-6 font-bold" />
                {t('Filter')}
            </LabelLink>

            <SortingBar className="flex-1" {...sortingBarProps} />
        </div>
    ) : (
        <SkeletonModuleFilterAndSortingBar />
    );
};
