'use client';

import { FilterPanelProps } from './FilterPanel';
import { Overlay } from 'app/_components/Basic/Overlay/Overlay';
import { SkeletonModuleFilterPanel } from 'components/Blocks/Skeleton/SkeletonModuleFilterPanel';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';

const FilterPanel = dynamic(() => import('./FilterPanel').then((mod) => ({ default: mod.FilterPanel })), {
    ssr: false,
    loading: () => <SkeletonModuleFilterPanel />,
});

export const FilterPanelSection: FC<FilterPanelProps> = (props) => {
    const { isFilterPanelOpen, setIsFilterPanelOpen } = useSessionStore((s) => ({
        isFilterPanelOpen: s.isFilterPanelOpen,
        setIsFilterPanelOpen: s.setIsFilterPanelOpen,
    }));

    return (
        <>
            <aside
                className={twJoin(
                    'max-vl:z-aboveOverlay vl:static vl:w-[227px] vl:translate-x-0 vl:rounded-none vl:transition-none fixed top-0 right-10 bottom-0 left-0 max-w-[400px] -translate-x-full overflow-hidden transition',
                    isFilterPanelOpen && 'translate-x-0',
                )}
            >
                <FilterPanel {...props} />
            </aside>

            {isFilterPanelOpen && <Overlay isActive={isFilterPanelOpen} onClick={() => setIsFilterPanelOpen(false)} />}
        </>
    );
};
