import { FilteredProductsWrapper } from 'components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { DeferredFilterSelectedParameters } from 'components/Blocks/Product/Filter/DeferredFilterSelectedParameters';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { SearchProductsContent } from './SearchProductsContent';
import { useSearchProductsData } from './searchUtils';

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
                <p className="h5 mb-2">{t('Found products')}</p>
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

                <div
                    className="flex flex-1 scroll-mt-5 flex-col gap-5"
                    id="product-list"
                    ref={paginationScrollTargetRef}
                    tabIndex={-1}
                >
                    <div className="flex vl:flex-col flex-col-reverse">
                        <DeferredFilterSelectedParameters filterOptions={searchProductsData.productFilterOptions} />

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

                    <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                        <SearchProductsContent
                            areSearchProductsFetching={areSearchProductsFetching}
                            isLoadingMoreSearchProducts={isLoadingMoreSearchProducts}
                            searchProductsData={searchProductsData}
                        />
                    </PaginationProvider>
                </div>
            </FilteredProductsWrapper>
        </div>
    );
};
