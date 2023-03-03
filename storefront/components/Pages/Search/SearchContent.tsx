import { ProductsSearch } from './ProductsSearch';
import { Heading } from 'components/Basic/Heading/Heading';
import { HeadingPaginated } from 'components/Basic/Heading/HeadingPaginated';
import { PaginationProvider } from 'components/Blocks/Pagination/PaginationProvider';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { Button } from 'components/Forms/Button/Button';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { desktopFirstSizes, mobileFirstSizes } from 'components/Theme/mediaQueries';
import { BreadcrumbFragmentApi, SearchQueryApi, SimpleCategoryFragmentApi } from 'graphql/generated';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import { getNewPagination } from 'helpers/pagination/getNewPagination';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useComponentUpdate } from 'hooks/helpers/useComponentUpdate';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useRouter } from 'next/router';
import { useMemo, useState } from 'react';
import { twJoin } from 'tailwind-merge';

enum NUMBER_OF_VISIBLE_ITEMS {
    XL = 8,
    NOT_LARGE_DESKTOP = 6,
    MOBILE_XS = 4,
}

type SearchContentProps = {
    searchResults: SearchQueryApi | undefined;
    breadcrumbs: BreadcrumbFragmentApi[];
};

export const SearchContent: FC<SearchContentProps> = ({ searchResults, breadcrumbs }) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const { width } = useGetWindowSize();
    const currentPage = parsePageNumberFromQuery(router.query[PAGE_QUERY_PARAMETER_NAME]);
    const [areArticlesResultsVisible, setArticlesResultsVisibility] = useState(false);
    const [areBrandsResultsVisible, setBrandsResultsVisibility] = useState(false);
    const [areCategoriesResultsVisible, setCategoriesResultsVisibility] = useState(false);
    const [numberOfVisible, setNumberOfVisible] = useState(0);
    const [oldRouterQuery, setOldRouterQuery] = useState(router.query.q);
    const [queryPathWasChanged, setQueryPathWasChanged] = useState(false);
    const [routerQueryChanged, setRouterQueryChanged] = useState(false);
    const mappedCategoriesSearchResults = useMemo(
        () => mapConnectionEdges<SimpleCategoryFragmentApi>(searchResults?.categoriesSearch.edges),
        [searchResults?.categoriesSearch.edges],
    );

    useResizeWidthEffect(
        width,
        desktopFirstSizes.notLargeDesktop,
        () => setNumberOfVisible(NUMBER_OF_VISIBLE_ITEMS.NOT_LARGE_DESKTOP),
        () => setNumberOfVisible(NUMBER_OF_VISIBLE_ITEMS.MOBILE_XS),
        () =>
            setNumberOfVisible(() => {
                if (width > mobileFirstSizes.xl) {
                    return NUMBER_OF_VISIBLE_ITEMS.XL;
                } else if (width < desktopFirstSizes.mobileXs) {
                    return NUMBER_OF_VISIBLE_ITEMS.MOBILE_XS;
                }
                return NUMBER_OF_VISIBLE_ITEMS.NOT_LARGE_DESKTOP;
            }),
    );

    useResizeWidthEffect(
        width,
        mobileFirstSizes.xl,
        () => setNumberOfVisible(NUMBER_OF_VISIBLE_ITEMS.XL),
        () => setNumberOfVisible(NUMBER_OF_VISIBLE_ITEMS.NOT_LARGE_DESKTOP),
    );

    useComponentUpdate(() => {
        if (oldRouterQuery !== router.query.q) {
            setQueryPathWasChanged(true);
            setOldRouterQuery(router.query.q);
        }
    }, [router.query.q]);

    useComponentUpdate(() => {
        if (queryPathWasChanged) {
            setRouterQueryChanged(!routerQueryChanged);
            setQueryPathWasChanged(false);
        }
    }, [searchResults?.productsSearch.productFilterOptions]);

    if (searchResults === undefined) {
        return null;
    }

    return (
        <>
            <Breadcrumbs breadcrumb={breadcrumbs} />
            <Webline>
                <Heading type="h1">{`${t('Search results for')} "${getStringFromUrlQuery(router.query.q)}"`}</Heading>
            </Webline>
            {currentPage === 1 && (
                <>
                    {searchResults.articlesSearch.length > 0 && (
                        <SearchResultsWebline>
                            <Heading type="h3">{t('Found articles')}</Heading>
                            <SearchResultsBlock areAllResultsVisible={areArticlesResultsVisible}>
                                <SimpleNavigation
                                    listedItems={searchResults.articlesSearch}
                                    imageType="searchThumbnail"
                                />
                            </SearchResultsBlock>
                            {numberOfVisible < searchResults.articlesSearch.length && (
                                <ShowResultsButtonWrapper>
                                    <Button
                                        isSmall
                                        onClick={() => {
                                            setArticlesResultsVisibility((currentState) => !currentState);
                                        }}
                                    >
                                        {areArticlesResultsVisible ? t('Hide results') : t('Show all results')}
                                    </Button>
                                </ShowResultsButtonWrapper>
                            )}
                        </SearchResultsWebline>
                    )}
                    {searchResults.brandSearch.length > 0 && (
                        <SearchResultsWebline>
                            <Heading type="h3">{t('Found brands')}</Heading>
                            <SearchResultsBlock areAllResultsVisible={areBrandsResultsVisible}>
                                <SimpleNavigation listedItems={searchResults.brandSearch} />
                            </SearchResultsBlock>
                            {numberOfVisible < searchResults.brandSearch.length && (
                                <ShowResultsButtonWrapper>
                                    <Button
                                        isSmall
                                        onClick={() => {
                                            setBrandsResultsVisibility((currentState) => !currentState);
                                        }}
                                    >
                                        {areBrandsResultsVisible ? t('Hide results') : t('Show all results')}
                                    </Button>
                                </ShowResultsButtonWrapper>
                            )}
                        </SearchResultsWebline>
                    )}
                    {searchResults.categoriesSearch.totalCount > 0 && mappedCategoriesSearchResults !== undefined && (
                        <SearchResultsWebline>
                            <Heading type="h3">{t('Found categories')}</Heading>
                            <SearchResultsBlock areAllResultsVisible={areCategoriesResultsVisible}>
                                <SimpleNavigation listedItems={mappedCategoriesSearchResults} />
                            </SearchResultsBlock>
                            {numberOfVisible < mappedCategoriesSearchResults.length && (
                                <ShowResultsButtonWrapper>
                                    <Button
                                        isSmall
                                        onClick={() => {
                                            setCategoriesResultsVisibility((currentState) => !currentState);
                                        }}
                                    >
                                        {areCategoriesResultsVisible ? t('Hide results') : t('Show all results')}
                                    </Button>
                                </ShowResultsButtonWrapper>
                            )}
                        </SearchResultsWebline>
                    )}
                </>
            )}
            <SearchResultsWebline>
                <PaginationProvider {...getNewPagination(currentPage)}>
                    <HeadingPaginated type="h3" totalCount={searchResults.productsSearch.totalCount}>
                        {t('Found products')}
                    </HeadingPaginated>
                    <ProductsSearch productsSearch={searchResults.productsSearch} />
                </PaginationProvider>
            </SearchResultsWebline>
        </>
    );
};

const SearchResultsBlock: FC<{ areAllResultsVisible: boolean }> = ({ children, areAllResultsVisible }) => (
    <div className={twJoin('lg:overflow-hidden', !areAllResultsVisible && 'lg:max-h-40')}>{children}</div>
);

const SearchResultsWebline: FC = ({ children }) => <Webline className="mt-6">{children}</Webline>;

const ShowResultsButtonWrapper: FC = ({ children }) => (
    <div className="my-5 hidden justify-center lg:flex">{children}</div>
);
