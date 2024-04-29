import { FilterPanelProps } from './FilterPanel';
import { SkeletonModuleFilterPanel } from 'components/Blocks/Skeleton/SkeletonModuleFilterPanel';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const FilterPanel = dynamic(() => import('./FilterPanel').then((component) => component.FilterPanel), {
    ssr: false,
    loading: () => <SkeletonModuleFilterPanel />,
});

export const DeferredFilterPanel: FC<FilterPanelProps> = (props) => {
    const shouldRender = useDeferredRender('filter_panel');

    return shouldRender ? <FilterPanel {...props} /> : <SkeletonModuleFilterPanel />;
};
