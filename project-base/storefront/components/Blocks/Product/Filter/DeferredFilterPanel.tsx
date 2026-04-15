import { SkeletonModuleFilterPanel } from 'components/Blocks/Skeleton/SkeletonModuleFilterPanel';
import dynamic from 'next/dynamic';
import { useSessionStore } from 'store/useSessionStore';
import { twJoin } from 'tailwind-merge';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { useDeferredRender } from 'utils/useDeferredRender';
import { FilterPanelProps } from './FilterPanel';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay), {
    ssr: false,
});

const FilterPanel = dynamic(() => import('./FilterPanel').then((component) => component.FilterPanel), {
    ssr: false,
    loading: () => <SkeletonModuleFilterPanel />,
});

export const DeferredFilterPanel: FC<FilterPanelProps> = (props) => {
    const { t } = useTranslation();
    const shouldRender = useDeferredRender('filter_panel');
    const { isFilterPanelOpen, setIsFilterPanelOpen } = useSessionStore((s) => ({
        isFilterPanelOpen: s.isFilterPanelOpen,
        setIsFilterPanelOpen: s.setIsFilterPanelOpen,
    }));

    return (
        <>
            <h2 className="sr-only">{t('Filter panel')}</h2>

            <div
                className={twJoin(
                    'fixed top-0 right-10 bottom-0 left-0 max-w-[400px] -translate-x-full transition',
                    'vl:static vl:w-[227px] vl:translate-x-0 vl:transition-none max-vl:z-aboveOverlay',
                    isFilterPanelOpen && 'translate-x-0',
                )}
            >
                {shouldRender ? <FilterPanel {...props} /> : <SkeletonModuleFilterPanel />}
            </div>

            {isFilterPanelOpen && <Overlay isActive={isFilterPanelOpen} onClick={() => setIsFilterPanelOpen(false)} />}
        </>
    );
};
