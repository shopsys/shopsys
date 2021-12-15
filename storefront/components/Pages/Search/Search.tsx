import { desktopFirstSizes, mobileFirstSizes } from 'components/Theme/mediaQueries';
import { FC, useRef, useState } from 'react';
import {
    SearchResultsBlockStyled,
    SearchResultsContentStyled,
    SearchResultsPanelStyled,
    SearchResultsStyled,
    SearchResultsWeblineStyled,
    ShowResultsButtonWrapperStyled,
} from './Search.style';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import Button from 'components/Forms/Button';
import { EnrichedSearchType } from 'types/search';
import Heading from 'components/Basic/Heading';
import Overlay from 'components/Basic/Overlay';
import Pagination from 'components/Blocks/Pagination';
import ProductsList from 'components/Blocks/Product/List/ProductsList';
import SimpleNavigation from 'components/Blocks/SimpleNavigation';
import SortingBar from 'components/Blocks/SortingBar';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useGetWindowSize } from 'hooks/ui/UseGetWindowSize';
import { useResizeWidthEffect } from 'hooks/ui/UseResizeWidthEffect';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

enum NUMBER_OF_VISIBLE_ITEMS {
    XL = 8,
    NOT_LARGE_DESKTOP = 6,
    MOBILE_XS = 4,
}

type SearchProps = {
    searchResults: EnrichedSearchType | undefined;
};

const Search: FC<SearchProps> = (props) => {
    const router = useRouter();
    const t = useTypedTranslationFunction();
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const panelWrapRef = useRef<null | HTMLDivElement>(null);
    const buttonRef = useRef<null | HTMLDivElement>(null);
    const domainUrl = useShopsysSelector((state) => state.domain.url);
    const [searchUrl] = useGetInternationalizedStaticUrls(['/search'], domainUrl);
    const { width } = useGetWindowSize();
    const { currentPage } = useShopsysSelector((state) => state.user.pagination);
    const [areArticlesResultsVisible, setArticlesResultsVisibility] = useState(false);
    const [areBrandsResultsVisible, setBrandsResultsVisibility] = useState(false);
    const [areCategoriesResultsVisible, setCategoriesResultsVisibility] = useState(false);
    const [numberOfVisible, setNumberOfVisible] = useState(0);
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

    if (props.searchResults === undefined) {
        return null;
    }

    return (
        <>
            <Breadcrumbs breadcrumb={[{ name: t('Search'), slug: searchUrl }]} />
            <Webline>
                <Heading type={'h1'}>{`${t('Search results for')} "${router.query.q}"`}</Heading>
            </Webline>
            {currentPage === 1 && (
                <>
                    {props.searchResults.articlesSearch.length > 0 && (
                        <SearchResultsWeblineStyled>
                            <Heading type={'h3'}>{t('Found articles')}</Heading>
                            <SearchResultsBlockStyled areAllResultsVisible={areArticlesResultsVisible}>
                                <SimpleNavigation listedItems={props.searchResults.articlesSearch} />
                            </SearchResultsBlockStyled>
                            {numberOfVisible < props.searchResults.articlesSearch.length && (
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
                    {props.searchResults.brandSearch.length > 0 && (
                        <SearchResultsWeblineStyled>
                            <Heading type={'h3'}>{t('Found brands')}</Heading>
                            <SearchResultsBlockStyled areAllResultsVisible={areBrandsResultsVisible}>
                                <SimpleNavigation listedItems={props.searchResults.brandSearch} />
                            </SearchResultsBlockStyled>
                            {numberOfVisible < props.searchResults.brandSearch.length && (
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
                    {props.searchResults.categoriesSearch.totalCount > 0 && (
                        <SearchResultsWeblineStyled>
                            <Heading type={'h3'}>{t('Found categories')}</Heading>
                            <SearchResultsBlockStyled areAllResultsVisible={areCategoriesResultsVisible}>
                                <SimpleNavigation listedItems={props.searchResults.categoriesSearch.categories} />
                            </SearchResultsBlockStyled>
                            {numberOfVisible < props.searchResults.categoriesSearch.categories.length && (
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
            {props.searchResults.productsSearch.totalCount > 0 && (
                <SearchResultsWeblineStyled>
                    <Heading type={'h3'}>{t('Found products')}</Heading>
                    <SearchResultsStyled>
                        <SearchResultsPanelStyled>
                            <Overlay isHiddenOnDesktop={true} onClick={handlePanelOpenerClick} />
                        </SearchResultsPanelStyled>
                        <SearchResultsContentStyled>
                            <SortingBar totalCount={props.searchResults.productsSearch.totalCount} />
                            <ProductsList products={props.searchResults.productsSearch.products} />
                            <Pagination totalCount={props.searchResults.productsSearch.totalCount} />
                        </SearchResultsContentStyled>
                    </SearchResultsStyled>
                </SearchResultsWeblineStyled>
            )}
        </>
    );
};

export default Search;
