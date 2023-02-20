import {
    SearchResultsContentStyled,
    SearchResultsPanelOpenerStyled,
    SearchResultsPanelStyled,
    SearchResultsStyled,
} from '../SearchContent.style';
import { SearchProductsWrapper } from '../SearchProductsWrapper';
import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { Icon } from 'components/Basic/Icon/Icon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { FilterProvider } from 'components/Blocks/Product/Filter/FilterContext/FilterProvider';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel/FilterPanel';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { SEARCH_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
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
                <FilterProvider
                    key={getStringFromUrlQuery(router.query[SEARCH_QUERY_PARAMETER_NAME])}
                    originalSlug={null}
                    productFilterOptions={productsSearch.productFilterOptions}
                >
                    <Webline>
                        {isFiltered && <MetaRobots content="noindex, follow" />}
                        <SearchResultsStyled ref={containerWrapRef}>
                            <SearchResultsPanelStyled isOpen={isPanelOpen}>
                                <FilterPanel
                                    defaultOrderingMode={productsSearch.defaultOrderingMode}
                                    orderingMode={productsSearch.orderingMode}
                                    originalSlug={null}
                                    panelCloseHandler={handlePanelOpenerClick}
                                    slug={searchUrl}
                                    totalCount={productsSearch.totalCount}
                                />
                            </SearchResultsPanelStyled>
                            {isPanelOpen && <Overlay $isHiddenOnDesktop onClick={handlePanelOpenerClick} />}
                            <SearchResultsContentStyled>
                                <SearchResultsPanelOpenerStyled onClick={handlePanelOpenerClick}>
                                    <Icon
                                        iconType="icon"
                                        icon="Filter"
                                        width={24}
                                        height={24}
                                        className="mr-3 font-bold text-white"
                                    />
                                    {t('Filter')}
                                </SearchResultsPanelOpenerStyled>
                                <SortingBar
                                    sorting={productsSearch.orderingMode}
                                    totalCount={productsSearch.totalCount}
                                />
                                <SearchProductsWrapper containerWrapperRef={containerWrapRef} />
                            </SearchResultsContentStyled>
                        </SearchResultsStyled>
                    </Webline>
                </FilterProvider>
            )}
        </>
    );
};
