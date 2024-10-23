import { SearchProductsContent } from './SearchProductsContent';
import { useSearchProductsData } from './searchUtils';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel';
import { FilterSelectedParameters } from 'components/Blocks/Product/Filter/FilterSelectedParameters';
import { SkeletonModuleProductsList } from 'components/Blocks/Skeleton/SkeletonModuleProductsList';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRef, useState } from 'react';
import Skeleton from 'react-loading-skeleton';
import { twJoin } from 'tailwind-merge';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => ({
    default: component.Overlay
})));

export const SearchProducts: FC = () => {
    const { t } = useTranslation();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const [isPanelOpen, setIsPanelOpen] = useState(false);

    const { searchProductsData, areSearchProductsFetching, isLoadingMoreSearchProducts } = useSearchProductsData();

    const handlePanelOpenerClick = () => {
        const body = document.getElementsByTagName('body')[0];

        setIsPanelOpen((prev) => {
            const newValue = !prev;
            body.style.overflow = newValue ? 'hidden' : 'visible';

            return newValue;
        });
    };

    if (areSearchProductsFetching) {
        return (
            <>
                <Skeleton className="mb-5 h-11 w-1/4" />
                <SkeletonModuleProductsList isWithoutBestsellers isWithoutDescription isWithoutNavigation />;
            </>
        );
    }

    if (!searchProductsData) {
        return null;
    }

    return (
        <>
            <h5 className="mb-2 mt-5 lg:my-9">{t('Found products')}</h5>

            <div className="mb-8 flex scroll-mt-5 flex-col vl:mb-10 vl:flex-row vl:flex-wrap vl:gap-4">
                <div
                    className={twJoin(
                        'fixed bottom-0 left-0 right-10 top-0 max-w-[400px] -translate-x-full overflow-hidden transition max-vl:z-aboveOverlay vl:static vl:w-[227px] vl:translate-x-0 vl:rounded-none vl:transition-none',
                        isPanelOpen && 'translate-x-0',
                    )}
                >
                    <FilterPanel
                        defaultOrderingMode={searchProductsData.defaultOrderingMode}
                        orderingMode={searchProductsData.orderingMode}
                        originalSlug={null}
                        panelCloseHandler={handlePanelOpenerClick}
                        productFilterOptions={searchProductsData.productFilterOptions}
                        slug={searchUrl}
                        totalCount={searchProductsData.totalCount}
                    />
                </div>

                <Overlay isActive={isPanelOpen} onClick={handlePanelOpenerClick} />

                <div className="flex flex-1 flex-col" ref={paginationScrollTargetRef}>
                    <div className="flex flex-col-reverse vl:flex-col">
                        <FilterSelectedParameters filterOptions={searchProductsData.productFilterOptions} />

                        <DeferredFilterAndSortingBar
                            handlePanelOpenerClick={handlePanelOpenerClick}
                            sorting={searchProductsData.orderingMode}
                            totalCount={searchProductsData.totalCount}
                            customSortOptions={[
                                TypeProductOrderingModeEnum.Relevance,
                                TypeProductOrderingModeEnum.PriceAsc,
                                TypeProductOrderingModeEnum.PriceDesc,
                            ]}
                        />
                    </div>

                    <SearchProductsContent
                        areSearchProductsFetching={areSearchProductsFetching}
                        isLoadingMoreSearchProducts={isLoadingMoreSearchProducts}
                        paginationScrollTargetRef={paginationScrollTargetRef}
                        searchProductsData={searchProductsData}
                    />
                </div>
            </div>
        </>
    );
};
