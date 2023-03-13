import { SearchProductsWrapper } from './SearchProductsWrapper';
import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { Icon } from 'components/Basic/Icon/Icon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { FilterProvider } from 'components/Blocks/Product/Filter/FilterContext/FilterProvider';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel/FilterPanel';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { ListedProductConnectionPreviewFragmentApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { getStringFromUrlQuery } from 'helpers/parsing/getStringFromUrlQuery';
import { SEARCH_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { useCallback, useRef, useState } from 'react';
import { useShopsysSelector } from 'redux/main';
import { twJoin } from 'tailwind-merge';

type ProductsSearchProps = {
    productsSearch: ListedProductConnectionPreviewFragmentApi;
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
        <FilterProvider
            key={getStringFromUrlQuery(router.query[SEARCH_QUERY_PARAMETER_NAME])}
            originalSlug={null}
            productFilterOptions={productsSearch.productFilterOptions}
        >
            <Webline>
                {isFiltered && <MetaRobots content="noindex, follow" />}
                <div className="relative mb-8 flex flex-col vl:mb-10 vl:flex-row vl:flex-wrap">
                    <div
                        className={twJoin(
                            'fixed top-0 left-0 bottom-0 right-10 max-w-md -translate-x-full vl:static vl:w-80 vl:translate-x-0 vl:transition-none',
                            isPanelOpen && 'z-aboveOverlay translate-x-0 transition',
                        )}
                    >
                        <FilterPanel
                            defaultOrderingMode={productsSearch.defaultOrderingMode}
                            orderingMode={productsSearch.orderingMode}
                            originalSlug={null}
                            panelCloseHandler={handlePanelOpenerClick}
                            slug={searchUrl}
                            totalCount={productsSearch.totalCount}
                        />
                    </div>
                    {isPanelOpen && <Overlay isHiddenOnDesktop onClick={handlePanelOpenerClick} />}
                    <div className="flex flex-1 flex-col">
                        <div
                            className="relative mb-3 flex h-12 w-full cursor-pointer flex-row justify-center rounded-xl bg-primary py-3 px-8 font-bold uppercase leading-7 text-white sm:w-44 vl:hidden"
                            onClick={handlePanelOpenerClick}
                        >
                            <Icon
                                iconType="icon"
                                icon="Filter"
                                width={24}
                                height={24}
                                className="mr-3 font-bold text-white"
                            />
                            {t('Filter')}
                        </div>
                        <SortingBar sorting={productsSearch.orderingMode} totalCount={productsSearch.totalCount} />
                        <SearchProductsWrapper containerWrapperRef={containerWrapRef} />
                    </div>
                </div>
            </Webline>
        </FilterProvider>
    );
};
