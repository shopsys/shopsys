import { ResultProducts } from './ResultProducts/ResultProducts';
import {
    SearchResultsBlockStyled,
    SearchResultsContentStyled,
    SearchResultsPanelStyled,
    SearchResultsStyled,
    SearchResultsWeblineStyled,
    ShowResultsButtonWrapperStyled,
} from './SearchContent.style';
import { Heading } from 'components/Basic/Heading/Heading';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Pagination } from 'components/Blocks/Pagination/Pagination';
import { ProductFilter } from 'components/Blocks/Product/Filter/Filter';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Button } from 'components/Forms/Button/Button';
import { Breadcrumbs } from 'components/Layout/Breadcrumbs/Breadcrumbs';
import { Webline } from 'components/Layout/Webline/Webline';
import { desktopFirstSizes, mobileFirstSizes } from 'components/Theme/mediaQueries';
import { useComponentUpdate } from 'hooks/helpers/UseComponentUpdate';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { useRouter } from 'next/router';
import { FC, useRef, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { SearchType } from 'types/search';
import { getInternationalizedStaticUrls } from 'utils/getInternationalizedStaticUrls';
import { getStringFromUrlQuery } from 'utils/getStringFromUrlQuery';

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
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const panelWrapRef = useRef<null | HTMLDivElement>(null);
    const buttonRef = useRef<null | HTMLDivElement>(null);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], domainUrl);
    const { width } = useGetWindowSize();
    const { currentPage } = useShopsysSelector((state) => state.user.pagination);
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

    const handlePanelOpenerClick = () => {
        setIsPanelOpen(!isPanelOpen);

        let newPosition = 0;
        const newPositionOffset = 20;

        if (buttonRef.current !== null) {
            newPosition = buttonRef.current.offsetTop + buttonRef.current.clientHeight + newPositionOffset;
        }

        if (panelWrapRef.current !== null) {
            panelWrapRef.current.style.cssText = 'top: ' + newPosition + 'px';
        }
    };

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
                <SearchResultsStyled ref={containerWrapRef}>
                    {searchResults.productsSearch.productFilterOptions?.maximalPrice !== 0 &&
                        searchResults.productsSearch.productFilterOptions !== null && (
                            <SearchResultsPanelStyled>
                                <ProductFilter
                                    productFilterOptions={searchResults.productsSearch.productFilterOptions}
                                    slug={searchUrl}
                                    originalSlug={null}
                                    orderingMode={searchResults.productsSearch.orderingMode}
                                />
                                <Overlay isHiddenOnDesktop={true} onClick={handlePanelOpenerClick} />
                            </SearchResultsPanelStyled>
                        )}
                    <SearchResultsContentStyled
                        isPanelActive={searchResults.productsSearch.productFilterOptions?.maximalPrice !== 0}
                    >
                        <SortingBar
                            sorting={searchResults.productsSearch.orderingMode}
                            totalCount={searchResults.productsSearch.totalCount}
                        />
                        <ResultProducts
                            products={searchResults.productsSearch.products}
                            areProductsShowed={searchResults.productsSearch.totalCount > 0}
                            noProductsFound={searchResults.productsSearch.productFilterOptions?.maximalPrice === 0}
                        />
                        <Pagination
                            totalCount={searchResults.productsSearch.totalCount}
                            containerWrapRef={containerWrapRef}
                        />
                    </SearchResultsContentStyled>
                </SearchResultsStyled>
            </SearchResultsWeblineStyled>
        </>
    );
};
