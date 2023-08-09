import { SearchProductsWrapper } from './SearchProductsWrapper';
import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { Icon } from 'components/Basic/Icon/Icon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { ListedProductConnectionPreviewFragmentApi, ProductOrderingModeEnumApi } from 'graphql/generated';
import { getInternationalizedStaticUrls } from 'helpers/localization/getInternationalizedStaticUrls';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useDomainConfig } from 'hooks/useDomainConfig';
import { useRouter } from 'next/router';
import { useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';

type ProductsSearchProps = {
    productsSearch: ListedProductConnectionPreviewFragmentApi;
};

export const ProductsSearch: FC<ProductsSearchProps> = ({ productsSearch }) => {
    const t = useTypedTranslationFunction();
    const router = useRouter();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const { url } = useDomainConfig();
    const [searchUrl] = getInternationalizedStaticUrls(['/search'], url);
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const isFiltered = 'filter' in router.query;

    const handlePanelOpenerClick = () => {
        const body = document.getElementsByTagName('body')[0];

        setIsPanelOpen((prev) => {
            const newValue = !prev;
            body.style.overflow = newValue ? 'hidden' : 'visible';

            return newValue;
        });
    };

    return (
        <>
            {isFiltered && <MetaRobots content="noindex, follow" />}
            <div className="relative mb-8 flex flex-col vl:mb-10 vl:flex-row vl:flex-wrap">
                <div
                    className={twJoin(
                        'fixed top-0 left-0 bottom-0 right-10 max-w-md -translate-x-full vl:static vl:w-80 vl:translate-x-0 vl:transition-none',
                        isPanelOpen && 'z-aboveOverlay translate-x-0 transition',
                    )}
                >
                    <FilterPanel
                        productFilterOptions={productsSearch.productFilterOptions}
                        defaultOrderingMode={productsSearch.defaultOrderingMode}
                        orderingMode={productsSearch.orderingMode}
                        originalSlug={null}
                        panelCloseHandler={handlePanelOpenerClick}
                        slug={searchUrl}
                        totalCount={productsSearch.totalCount}
                    />
                </div>
                <Overlay isActive={isPanelOpen} onClick={handlePanelOpenerClick} />
                <div className="flex flex-1 flex-col vl:pl-12" ref={paginationScrollTargetRef}>
                    <div
                        className="relative mb-3 flex h-12 w-full cursor-pointer flex-row justify-center rounded-xl bg-primary py-3 px-8 font-bold uppercase leading-7 text-white vl:hidden"
                        onClick={handlePanelOpenerClick}
                    >
                        <Icon iconType="icon" icon="Filter" className="mr-3 w-6 font-bold text-white" />
                        {t('Filter')}
                    </div>
                    <SortingBar
                        sorting={productsSearch.orderingMode}
                        totalCount={productsSearch.totalCount}
                        customSortOptions={[
                            ProductOrderingModeEnumApi.RelevanceApi,
                            ProductOrderingModeEnumApi.PriceAscApi,
                            ProductOrderingModeEnumApi.PriceDescApi,
                        ]}
                    />
                    <SearchProductsWrapper paginationScrollTargetRef={paginationScrollTargetRef} />
                </div>
            </div>
        </>
    );
};
