import { SkeletonModuleFilterAndSortingBar } from 'components/Blocks/Skeleton/SkeletonModuleFilterAndSortingBar';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';
import { SortingBarProps } from './SortingBar';

const SortingBar = dynamic(() => import('./SortingBar').then((component) => component.SortingBar), {
    ssr: false,
    loading: () => <SkeletonModuleFilterAndSortingBar />,
});

export const DeferredFilterAndSortingBar: FC<SortingBarProps> = ({ ...sortingBarProps }) => {
    const shouldRender = useDeferredRender('sorting_bar');

    return shouldRender ? <SortingBar {...sortingBarProps} /> : <SkeletonModuleFilterAndSortingBar />;
};
