import { SearchResultsContentStyled, SearchResultsPanelStyled, SearchResultsStyled } from '../SearchContent.style';
import { SearchProductsWrapper } from '../SearchProductsWrapper';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { PaginationProvider } from 'components/Blocks/Pagination/PaginationProvider';
import { Filter } from 'components/Blocks/Product/Filter/Filter';
import { FilterProvider } from 'components/Blocks/Product/Filter/FilterContext/FilterProvider';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME, SEARCH_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useRouter } from 'next/router';
import { FC, useRef } from 'react';
import { useShopsysSelector } from 'redux/main';
import { ListedProductConnectionPreviewType } from 'types/product';

type ProductsSearchProps = {
    productsSearch: ListedProductConnectionPreviewType;
};

export const ProductsSearch: FC<ProductsSearchProps> = ({ productsSearch }) => {
    const router = useRouter();
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);
    const containerWrapRef = useRef<HTMLDivElement>(null);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], domainUrl);

    return (
        <>
            {productsSearch.productFilterOptions !== null && (
                <PaginationProvider {...getNewPagination(currentPage)}>
                    <FilterProvider
                        key={getStringFromUrlQuery(router.query[SEARCH_QUERY_PARAMETER_NAME])}
                        originalSlug={null}
                        productFilterOptions={productsSearch.productFilterOptions}
                    >
                        <SearchResultsStyled ref={containerWrapRef}>
                            <SearchResultsPanelStyled>
                                <Filter
                                    slug={searchUrl}
                                    originalSlug={null}
                                    orderingMode={productsSearch.orderingMode}
                                />
                            </SearchResultsPanelStyled>
                            <SearchResultsContentStyled
                                isPanelActive={productsSearch.productFilterOptions.maximalPrice !== 0}
                            >
                                <SortingBar
                                    sorting={productsSearch.orderingMode}
                                    totalCount={productsSearch.totalCount}
                                />
                                <SearchProductsWrapper containerWrapperRef={containerWrapRef} />
                                <Pagination
                                    totalCount={productsSearch.totalCount}
                                    containerWrapRef={containerWrapRef}
                                />
                            </SearchResultsContentStyled>
                        </SearchResultsStyled>
                    </FilterProvider>
                </PaginationProvider>
            )}
        </>
    );
};
