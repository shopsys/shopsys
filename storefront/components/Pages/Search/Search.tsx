import { FC, useRef, useState } from 'react';
import { SearchResultsContentStyled, SearchResultsStyled, SeatchResultsPanelStyled } from './Search.style';
import Breadcrumbs from 'components/Layout/Breadcrumbs';
import { EnrichedSearchType } from 'connectors/search/types';
import Heading from 'components/Basic/Heading';
import Overlay from 'components/Basic/Overlay';
import Pagination from 'components/Blocks/Pagination';
import ProductFilter from 'components/Blocks/Product/Filter';
import ProductsList from 'components/Blocks/Product/List/ProductsList';
import SortingBar from 'components/Blocks/SortingBar';
import { useGetInternationalizedStaticUrls } from 'hooks/staticUrls/UseGetInternationalizedStaticUrls';
import { useRouter } from 'next/router';
import { useShopsysSelector } from 'redux/main';
import { useTypedTranslationFunction } from 'hooks/typescript/UseTypedTranslationFunction';
import Webline from 'components/Layout/Webline';

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
            {props.searchResults.productsSearch.totalCount > 0 && (
                <Webline>
                    <Heading type={'h3'}>{t('Found products')}</Heading>
                    <SearchResultsStyled>
                        <SeatchResultsPanelStyled>
                            <ProductFilter />
                            <Overlay isHiddenOnDesktop={true} onClick={handlePanelOpenerClick} />
                        </SeatchResultsPanelStyled>
                        <SearchResultsContentStyled>
                            <SortingBar totalCount={props.searchResults.productsSearch.totalCount} />
                            <ProductsList products={props.searchResults.productsSearch.products} />
                            <Pagination totalCount={props.searchResults.productsSearch.totalCount} />
                        </SearchResultsContentStyled>
                    </SearchResultsStyled>
                </Webline>
            )}
        </>
    );
};

export default Search;
