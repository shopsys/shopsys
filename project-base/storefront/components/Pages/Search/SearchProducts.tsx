import { SearchProductsContent } from './SearchProductsContent';
import { useSearchProductsData } from './utils';
import { FilterIcon } from 'components/Basic/Icon/FilterIcon';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel';
import { SkeletonModuleProductsList } from 'components/Blocks/Skeleton/SkeletonModuleProductsList';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import dynamic from 'next/dynamic';
import { useRef, useState } from 'react';
import Skeleton from 'react-loading-skeleton';
import { twJoin } from 'tailwind-merge';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay));

export const SearchProducts: FC = () => {
    const { t } = useTranslation();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const [isPanelOpen, setIsPanelOpen] = useState(false);

    const { searchProductsData, isFetching, isLoadMoreFetching } = useSearchProductsData();

    const handlePanelOpenerClick = () => {
        const body = document.getElementsByTagName('body')[0];

        setIsPanelOpen((prev) => {
            const newValue = !prev;
            body.style.overflow = newValue ? 'hidden' : 'visible';

            return newValue;
        });
    };

    if (isFetching) {
        return (
            <>
                <Skeleton className="h-full" containerClassName="block h-7 w-72 mb-3" />
                <SkeletonModuleProductsList isWithoutDescription isWithoutNavigation />
            </>
        );
    }

    if (!searchProductsData) {
        return null;
    }

    return (
        <>
            <div className="mt-6">
                <div className="h4 mb-3">{t('Found products')}</div>
            </div>

            <div className="relative mb-8 flex flex-col vl:mb-10 vl:flex-row vl:flex-wrap vl:gap-12">
                <div
                    className={twJoin(
                        'fixed top-0 left-0 bottom-0 right-10 max-w-md -translate-x-full vl:static vl:w-80 vl:translate-x-0 vl:transition-none',
                        isPanelOpen && 'z-aboveOverlay translate-x-0 transition',
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
                    <div
                        className="relative mb-3 flex h-12 w-full cursor-pointer flex-row justify-center rounded bg-primary py-3 px-8 font-bold uppercase leading-7 text-white vl:hidden"
                        onClick={handlePanelOpenerClick}
                    >
                        <FilterIcon className="mr-3 w-6 font-bold text-white" />
                        {t('Filter')}
                    </div>

                    <SortingBar
                        sorting={searchProductsData.orderingMode}
                        totalCount={searchProductsData.totalCount}
                        customSortOptions={[
                            TypeProductOrderingModeEnum.Relevance,
                            TypeProductOrderingModeEnum.PriceAsc,
                            TypeProductOrderingModeEnum.PriceDesc,
                        ]}
                    />

                    <SearchProductsContent
                        isFetching={isFetching}
                        isLoadMoreFetching={isLoadMoreFetching}
                        paginationScrollTargetRef={paginationScrollTargetRef}
                        searchProductsData={searchProductsData}
                    />
                </div>
            </div>
        </>
    );
};
