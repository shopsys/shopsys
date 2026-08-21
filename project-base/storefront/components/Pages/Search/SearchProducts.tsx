import { FilteredProductsWrapper } from 'components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { DeferredFilterSelectedParameters } from 'components/Blocks/Product/Filter/DeferredFilterSelectedParameters';
import { PRODUCT_LIST_HEADING_ELEMENT_ID } from 'components/Blocks/Product/Filter/filterElementIds';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { TIDs } from 'cypress/tids';
import { TypeSearchProductsQuery } from 'graphql/requests/search/queries/SearchProductsQuery.generated';
import { TypeProductOrderingModeEnum } from 'graphql/types';
import { useRef } from 'react';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { SearchProductsContent } from './SearchProductsContent';
import { useSearchProductsData } from './searchUtils';

type SearchProductsProps = {
    searchProductsDataFromMainQuery?: TypeSearchProductsQuery['productsSearch'];
};

export const SearchProducts: FC<SearchProductsProps> = ({ searchProductsDataFromMainQuery }) => {
    const { t } = useTranslation();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);

    const { searchProductsData, areSearchProductsFetching, isLoadingMoreSearchProducts } = useSearchProductsData({
        searchProductsDataFromMainQuery,
    });

    if (!searchProductsData) {
        return null;
    }

    return (
        <div>
            <Webline>
                <p className="h5 mb-2 scroll-mt-fixed-header" id={PRODUCT_LIST_HEADING_ELEMENT_ID}>
                    {t('Found products')}
                </p>
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
                    className="flex flex-1 scroll-mt-fixed-header flex-col gap-5 focus-visible:outline-hidden"
                    data-tid={TIDs.product_list}
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
