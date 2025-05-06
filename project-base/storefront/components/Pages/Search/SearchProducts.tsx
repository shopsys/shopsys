import { SearchProductsContent } from './SearchProductsContent';
import { useSearchProductsData } from './searchUtils';
import { FilteredProductsWrapper } from 'components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { FilterSelectedParameters } from 'components/Blocks/Product/Filter/FilterSelectedParameters';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import useTranslation from 'next-translate/useTranslation';
import { useRef } from 'react';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const SearchProducts: FC = () => {
    const { t } = useTranslation();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);

    const { searchProductsData, areSearchProductsFetching, isLoadingMoreSearchProducts } = useSearchProductsData();

    if (!searchProductsData) {
        return null;
    }

    return (
        <div>
            <Webline>
                <h5 className="mb-2">{t('Found products')}</h5>
            </Webline>

            <FilteredProductsWrapper>
                <DeferredFilterPanel
                    defaultOrderingMode={searchProductsData.defaultOrderingMode}
                    orderingMode={searchProductsData.orderingMode}
                    originalSlug={null}
                    productFilterOptions={searchProductsData.productFilterOptions}
                    slug={searchUrl}
                    totalCount={searchProductsData.totalCount}
                />

                <div className="flex flex-1 scroll-mt-5 flex-col gap-5" ref={paginationScrollTargetRef}>
                    <div className="vl:flex-col flex flex-col-reverse">
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
        </div>
    );
};
