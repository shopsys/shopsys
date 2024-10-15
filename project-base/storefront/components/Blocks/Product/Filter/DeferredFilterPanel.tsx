import { FilterPanelProps } from './FilterPanel';
import { SkeletonModuleFilterPanel } from 'components/Blocks/Skeleton/SkeletonModuleFilterPanel';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import { useDeferredRender } from 'utils/useDeferredRender';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay), {
    ssr: false,
});

const FilterPanel = dynamic(() => import('./FilterPanel').then((component) => component.FilterPanel), {
    ssr: false,
    loading: () => <SkeletonModuleFilterPanel />,
});

export const DeferredFilterPanel: FC<FilterPanelProps> = (props) => {
    const shouldRender = useDeferredRender('filter_panel');
    const { isFilterPanelOpen, setIsFilterPanelOpen } = useSessionStore((s) => ({
        isFilterPanelOpen: s.isFilterPanelOpen,
        setIsFilterPanelOpen: s.setIsFilterPanelOpen,
    }));

    return (
        <>
            <div
                className={twJoin(
                    'fixed bottom-0 left-0 right-10 top-0 max-w-[400px] -translate-x-full overflow-hidden transition max-vl:z-aboveOverlay vl:static vl:w-[227px] vl:translate-x-0 vl:rounded-none vl:transition-none',
                    isFilterPanelOpen && 'translate-x-0',
                )}
            >
                {shouldRender ? <FilterPanel {...props} /> : <SkeletonModuleFilterPanel />}
            </div>

            {isFilterPanelOpen && <Overlay isActive={isFilterPanelOpen} onClick={() => setIsFilterPanelOpen(false)} />}
        </>
    );
};
