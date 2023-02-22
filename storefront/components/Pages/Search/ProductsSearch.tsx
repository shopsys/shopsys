import { SearchResults, SearchResultsContent, SearchResultsPanel, SearchResultsPanelOpener } from './SearchElements';
import { SearchProductsWrapper } from './SearchProductsWrapper';
import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { Icon } from 'components/Basic/Icon/Icon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { PaginationProvider } from 'components/Blocks/Pagination/PaginationProvider';
import { FilterProvider } from 'components/Blocks/Product/Filter/FilterContext/FilterProvider';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel/FilterPanel';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME, SEARCH_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { FC, useCallback, useRef, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { ListedProductConnectionPreviewType } from 'types/product';

type ProductsSearchProps = {
    productsSearch: ListedProductConnectionPreviewType;
};

export const ProductsSearch: FC<ProductsSearchProps> = ({ productsSearch }) => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);
    const containerWrapRef = useRef<HTMLDivElement>(null);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], domainUrl);
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const isFiltered = 'filter' in router.query;

    const handlePanelOpenerClick = useCallback(() => {
        const body = document.getElementsByTagName('body')[0];

        setIsPanelOpen((prev) => {
            body.style.overflow = prev ? 'visible' : 'hidden';
            return !prev;
        });
    }, []);

    return (
        <>
            {productsSearch.productFilterOptions !== null && (
                <PaginationProvider {...getNewPagination(currentPage)}>
                    <FilterProvider
                        key={getStringFromUrlQuery(router.query[SEARCH_QUERY_PARAMETER_NAME])}
                        originalSlug={null}
                        productFilterOptions={productsSearch.productFilterOptions}
                    >
                        <Webline>
                            {isFiltered && <MetaRobots content="noindex, follow" />}
                            <SearchResults>
                                <SearchResultsPanel isOpen={isPanelOpen}>
                                    <FilterPanel
                                        defaultOrderingMode={productsSearch.defaultOrderingMode}
                                        orderingMode={productsSearch.orderingMode}
                                        originalSlug={null}
                                        panelCloseHandler={handlePanelOpenerClick}
                                        slug={searchUrl}
                                        totalCount={productsSearch.totalCount}
                                    />
                                </SearchResultsPanel>
                                {isPanelOpen && <Overlay $isHiddenOnDesktop onClick={handlePanelOpenerClick} />}
                                <SearchResultsContent>
                                    <SearchResultsPanelOpener onClick={handlePanelOpenerClick}>
                                        <Icon
                                            iconType="icon"
                                            icon="Filter"
                                            width={24}
                                            height={24}
                                            className="mr-3 font-bold text-white"
                                        />
                                        {t('Filter')}
                                    </SearchResultsPanelOpener>
                                    <SortingBar
                                        sorting={productsSearch.orderingMode}
                                        totalCount={productsSearch.totalCount}
                                    />
                                    <SearchProductsWrapper containerWrapperRef={containerWrapRef} />
                                </SearchResultsContent>
                            </SearchResults>
                        </Webline>
                    </FilterProvider>
                </PaginationProvider>
            )}
        </>
    );
};
