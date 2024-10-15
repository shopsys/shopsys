import { SearchProductsContent } from './SearchProductsContent';
import { useSearchProductsData } from './searchUtils';
import { FilteredProductsWrapper } from 'components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { FilterSelectedParameters } from 'components/Blocks/Product/Filter/FilterSelectedParameters';
import { SkeletonModuleProductsList } from 'components/Blocks/Skeleton/SkeletonModuleProductsList';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';
import Skeleton from 'react-loading-skeleton';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const SearchProducts: FC = () => {
    const { t } = useTranslation();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);

    const { searchProductsData, areSearchProductsFetching, isLoadingMoreSearchProducts } = useSearchProductsData();

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

            <FilteredProductsWrapper ref={paginationScrollTargetRef}>
                <DeferredFilterPanel
                    defaultOrderingMode={searchProductsData.defaultOrderingMode}
                    orderingMode={searchProductsData.orderingMode}
                    originalSlug={null}
                    productFilterOptions={searchProductsData.productFilterOptions}
                    slug={searchUrl}
                    totalCount={searchProductsData.totalCount}
                />

                <div className="flex flex-1 flex-col" ref={paginationScrollTargetRef}>
                    <div className="flex flex-col-reverse vl:flex-col">
                        <FilterSelectedParameters filterOptions={searchProductsData.productFilterOptions} />

                        <DeferredFilterAndSortingBar
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
            </FilteredProductsWrapper>
        </>
    );
};
