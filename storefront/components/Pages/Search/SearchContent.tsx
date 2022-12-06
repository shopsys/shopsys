import { ProductsSearch } from './ProductsSearch/ProductsSearch';
import {
    SearchResultsBlockStyled,
    SearchResultsWeblineStyled,
    ShowResultsButtonWrapperStyled,
} from './SearchContent.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { Button } from 'components/Forms/Button/Button';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { desktopFirstSizes, mobileFirstSizes } from 'components/Theme/mediaQueries';
import { parsePageNumberFromQuery } from 'helpers/pagination/parsePageNumberFromQuery';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useComponentUpdate } from 'hooks/helpers/useComponentUpdate';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useRouter } from 'next/router';
import { FC, useState } from 'react';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { SearchType } from 'types/search';

enum NUMBER_OF_VISIBLE_ITEMS {
    XL = 8,
    NOT_LARGE_DESKTOP = 6,
    MOBILE_XS = 4,
}

type SearchContentProps = {
    searchResults: SearchType | undefined;
    breadcrumbs: BreadcrumbItemType[];
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
                <Heading type={'h1'}>{`${t('Search results for')} "${getStringFromUrlQuery(router.query.q)}"`}</Heading>
            </Webline>
            {currentPage === 1 && (
                <>
                    {searchResults.articlesSearch.length > 0 && (
                        <SearchResultsWeblineStyled>
                            <Heading type={'h3'}>{t('Found articles')}</Heading>
                            <SearchResultsBlockStyled areAllResultsVisible={areArticlesResultsVisible}>
                                <SimpleNavigation
                                    listedItems={searchResults.articlesSearch}
                                    imageType="searchThumbnail"
                                />
                            </SearchResultsBlockStyled>
                            {numberOfVisible < searchResults.articlesSearch.length && (
                                <ShowResultsButtonWrapperStyled>
                                    <Button
                                        type="button"
                                        size="small"
                                        onClick={() => {
                                            setArticlesResultsVisibility((currentState) => !currentState);
                                        }}
                                    >
                                        {areArticlesResultsVisible ? t('Hide results') : t('Show all results')}
                                    </Button>
                                </ShowResultsButtonWrapperStyled>
                            )}
                        </SearchResultsWeblineStyled>
                    )}
                    {searchResults.brandSearch.length > 0 && (
                        <SearchResultsWeblineStyled>
                            <Heading type={'h3'}>{t('Found brands')}</Heading>
                            <SearchResultsBlockStyled areAllResultsVisible={areBrandsResultsVisible}>
                                <SimpleNavigation listedItems={searchResults.brandSearch} />
                            </SearchResultsBlockStyled>
                            {numberOfVisible < searchResults.brandSearch.length && (
                                <ShowResultsButtonWrapperStyled>
                                    <Button
                                        type="button"
                                        size="small"
                                        onClick={() => {
                                            setBrandsResultsVisibility((currentState) => !currentState);
                                        }}
                                    >
                                        {areBrandsResultsVisible ? t('Hide results') : t('Show all results')}
                                    </Button>
                                </ShowResultsButtonWrapperStyled>
                            )}
                        </SearchResultsWeblineStyled>
                    )}
                    {searchResults.categoriesSearch.totalCount > 0 && (
                        <SearchResultsWeblineStyled>
                            <Heading type={'h3'}>{t('Found categories')}</Heading>
                            <SearchResultsBlockStyled areAllResultsVisible={areCategoriesResultsVisible}>
                                <SimpleNavigation listedItems={searchResults.categoriesSearch.categories} />
                            </SearchResultsBlockStyled>
                            {numberOfVisible < searchResults.categoriesSearch.categories.length && (
                                <ShowResultsButtonWrapperStyled>
                                    <Button
                                        type="button"
                                        size="small"
                                        onClick={() => {
                                            setCategoriesResultsVisibility((currentState) => !currentState);
                                        }}
                                    >
                                        {areCategoriesResultsVisible ? t('Hide results') : t('Show all results')}
                                    </Button>
                                </ShowResultsButtonWrapperStyled>
                            )}
                        </SearchResultsWeblineStyled>
                    )}
                </>
            )}
            <SearchResultsWeblineStyled>
                <Heading type={'h3'}>{t('Found products')}</Heading>
                <ProductsSearch productsSearch={searchResults.productsSearch} />
            </SearchResultsWeblineStyled>
        </>
    );
};
