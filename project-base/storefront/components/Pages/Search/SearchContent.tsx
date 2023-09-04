import { ProductsSearch } from './ProductsSearch';
import { Heading } from 'components/Basic/Heading/Heading';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { Button } from 'components/Forms/Button/Button';
import { Webline } from 'components/Layout/Webline/Webline';
import { desktopFirstSizes, mobileFirstSizes } from 'components/Theme/mediaQueries';
import { SearchQueryApi, SimpleCategoryFragmentApi } from 'graphql/generated';
import { mapConnectionEdges } from 'helpers/mappers/connection';
import useTranslation from 'next-translate/useTranslation';
import { useGetWindowSize } from 'hooks/ui/useGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/useResizeWidthEffect';
import { useRouter } from 'next/router';
import { useMemo, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { CategoryDetailPageSkeleton } from '../CategoryDetail/CategoryDetailPageSkeleton';
import { useSeoTitleWithPagination } from 'hooks/seo/useSeoTitleWithPagination';
import { getStringFromUrlQuery } from 'helpers/parsing/urlParsing';

enum NUMBER_OF_VISIBLE_ITEMS {
    XL = 8,
    NOT_LARGE_DESKTOP = 6,
    MOBILE_XS = 4,
}

type SearchContentProps = {
    searchResults: SearchQueryApi | undefined;
    fetching: boolean;
};

export const SearchContent: FC<SearchContentProps> = ({ searchResults, fetching }) => {
    const router = useRouter();
    const { t } = useTranslation();
    const { width } = useGetWindowSize();
    const [areArticlesResultsVisible, setArticlesResultsVisibility] = useState(false);
    const [areBrandsResultsVisible, setBrandsResultsVisibility] = useState(false);
    const [areCategoriesResultsVisible, setCategoriesResultsVisibility] = useState(false);
    const [numberOfVisible, setNumberOfVisible] = useState(0);

    const title = useSeoTitleWithPagination(searchResults?.productsSearch.totalCount, t('Found products'));

    const mappedCategoriesSearchResults = useMemo(
        () => mapConnectionEdges<SimpleCategoryFragmentApi>(searchResults?.categoriesSearch.edges),
        [searchResults?.categoriesSearch.edges],
    );

    const isFetchingInitialData = !searchResults && fetching;

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

    return (
        <Webline>
            <Heading type="h1">{`${t('Search results for')} "${getStringFromUrlQuery(router.query.q)}"`}</Heading>
            {isFetchingInitialData ? (
                <CategoryDetailPageSkeleton />
            ) : (
                !!searchResults && (
                    <>
                        {searchResults.articlesSearch.length > 0 && (
                            <div className="mt-6">
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
                                            size="small"
                                            onClick={() => {
                                                setArticlesResultsVisibility((currentState) => !currentState);
                                            }}
                                        >
                                            {areArticlesResultsVisible ? t('Hide results') : t('Show all results')}
                                        </Button>
                                    </ShowResultsButtonWrapper>
                                )}
                            </div>
                        )}

                        {searchResults.brandSearch.length > 0 && (
                            <div className="mt-6">
                                <Heading type="h3">{t('Found brands')}</Heading>
                                <SearchResultsBlock areAllResultsVisible={areBrandsResultsVisible}>
                                    <SimpleNavigation listedItems={searchResults.brandSearch} />
                                </SearchResultsBlock>
                                {numberOfVisible < searchResults.brandSearch.length && (
                                    <ShowResultsButtonWrapper>
                                        <Button
                                            size="small"
                                            onClick={() => {
                                                setBrandsResultsVisibility((currentState) => !currentState);
                                            }}
                                        >
                                            {areBrandsResultsVisible ? t('Hide results') : t('Show all results')}
                                        </Button>
                                    </ShowResultsButtonWrapper>
                                )}
                            </div>
                        )}

                        {searchResults.categoriesSearch.totalCount > 0 && !!mappedCategoriesSearchResults && (
                            <div className="mt-6">
                                <Heading type="h3">{t('Found categories')}</Heading>
                                <SearchResultsBlock areAllResultsVisible={areCategoriesResultsVisible}>
                                    <SimpleNavigation listedItems={mappedCategoriesSearchResults} />
                                </SearchResultsBlock>
                                {numberOfVisible < mappedCategoriesSearchResults.length && (
                                    <ShowResultsButtonWrapper>
                                        <Button
                                            size="small"
                                            onClick={() => {
                                                setCategoriesResultsVisibility((currentState) => !currentState);
                                            }}
                                        >
                                            {areCategoriesResultsVisible ? t('Hide results') : t('Show all results')}
                                        </Button>
                                    </ShowResultsButtonWrapper>
                                )}
                            </div>
                        )}

                        <div className="mt-6">
                            <Heading type="h3">{title}</Heading>
                            <ProductsSearch productsSearch={searchResults.productsSearch} />
                        </div>
                    </>
                )
            )}
        </Webline>
    );
};

const SearchResultsBlock: FC<{ areAllResultsVisible: boolean }> = ({ children, areAllResultsVisible }) => (
    <div className={twJoin('lg:overflow-hidden', !areAllResultsVisible && 'lg:max-h-36')}>{children}</div>
);

const ShowResultsButtonWrapper: FC = ({ children }) => (
    <div className="my-5 hidden justify-center lg:flex">{children}</div>
);
