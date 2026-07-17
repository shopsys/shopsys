import { Drawer } from 'components/Basic/Drawer/Drawer';
import { SkeletonModuleFilterPanel } from 'components/Blocks/Skeleton/SkeletonModuleFilterPanel';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useMediaMin } from 'utils/ui/useMediaMin';
import { useDeferredRender } from 'utils/useDeferredRender';
import { FilterPanelProps } from './FilterPanel';
import { scrollToSelectedFilters } from './filterElementIds';

const FilterPanel = dynamic(() => import('./FilterPanel').then((component) => component.FilterPanel), {
    ssr: false,
    loading: () => <SkeletonModuleFilterPanel />,
});

export const DeferredFilterPanel: FC<FilterPanelProps> = (props) => {
    const { t } = useTranslation();
    const shouldRender = useDeferredRender('filter_panel');
    const isDesktop = useMediaMin('vl');
    const { isFilterPanelOpen, setIsFilterPanelOpen } = useSessionStore((s) => ({
        isFilterPanelOpen: s.isFilterPanelOpen,
        setIsFilterPanelOpen: s.setIsFilterPanelOpen,
    }));
    const filterPanel = shouldRender ? <FilterPanel {...props} /> : <SkeletonModuleFilterPanel />;

    if (isDesktop === false) {
        return (
            <Drawer
                ariaLabel={t('Product filters', { ns: 'accessibility' })}
                className="w-[calc(100vw-2.5rem)] min-w-0 max-w-100 p-0"
                isActive={isFilterPanelOpen}
                onClose={scrollToSelectedFilters}
                setIsActive={setIsFilterPanelOpen}
                shouldRenderHeader={false}
            >
                {filterPanel}
            </Drawer>
        );
    }

    return <div className="vl:block hidden vl:w-56">{filterPanel}</div>;
};
