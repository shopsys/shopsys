import { AdvancedSeoCategories } from './AdvancedSeoCategories/AdvancedSeoCategories';
import { CategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper';
import { MetaRobots } from 'components/Basic/Head/MetaRobots/MetaRobots';
import { HeadingPaginated } from 'components/Basic/Heading/HeadingPaginated';
import { Icon } from 'components/Basic/Icon/Icon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { FilterProvider } from 'components/Blocks/Product/Filter/FilterContext/FilterProvider';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel/FilterPanel';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { CategoryDetailFragmentApi } from 'graphql/generated';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { useCallback, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';

type CategoryDetailContentProps = {
    category: CategoryDetailFragmentApi;
};

export const CategoryDetailContent: FC<CategoryDetailContentProps> = ({ category }) => {
    const t = useTypedTranslationFunction();
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const { query } = useRouter();
    const isFiltered = 'filter' in query;

    const handlePanelOpenerClick = useCallback(() => {
        const body = document.getElementsByTagName('body')[0];

        setIsPanelOpen((prev) => {
            body.style.overflow = prev ? 'visible' : 'hidden';
            return !prev;
        });
    }, []);

    return (
        <FilterProvider
            key={category.slug}
            originalSlug={category.originalCategorySlug}
            productFilterOptions={category.products.productFilterOptions}
        >
            <Webline>
                {isFiltered && <MetaRobots content="noindex, follow" />}
                <div className="mb-7 flex flex-col vl:mb-10 vl:flex-row vl:flex-wrap" ref={containerWrapRef}>
                    <div
                        className={twJoin(
                            'fixed top-0 left-0 bottom-0 right-10 max-w-[400px] -translate-x-full vl:static vl:w-[304px] vl:translate-x-0 vl:transition-none ',
                            isPanelOpen && 'z-aboveOverlay translate-x-0 transition',
                        )}
                    >
                        <FilterPanel
                            orderingMode={category.products.orderingMode}
                            defaultOrderingMode={category.products.defaultOrderingMode}
                            originalSlug={category.originalCategorySlug}
                            panelCloseHandler={handlePanelOpenerClick}
                            slug={category.slug}
                            totalCount={category.products.totalCount}
                        />
                    </div>
                    {isPanelOpen && <Overlay $isHiddenOnDesktop onClick={handlePanelOpenerClick} />}
                    <div className="flex flex-1 flex-col vl:pl-12">
                        <Adverts positionName="productList" className="mb-4" />
                        <HeadingPaginated type="h1" totalCount={category.products.totalCount}>
                            {category.seoH1 !== null ? category.seoH1 : category.name}
                        </HeadingPaginated>
                        {category.description !== null &&
                            category.description !== '' &&
                            (query[PAGE_QUERY_PARAMETER_NAME] ?? 1) === 1 && (
                                <div dangerouslySetInnerHTML={{ __html: category.description }} className="mb-4" />
                            )}
                        <Adverts positionName="productListMiddle" currentCategory={category} className="mb-4" />
                        <SimpleNavigation
                            listedItems={[...category.children, ...category.linkedCategories]}
                            className="mb-6"
                        />
                        <AdvancedSeoCategories readyCategorySeoMixLinks={category.readyCategorySeoMixLinks} />
                        <div
                            className="relative mb-3 flex min-h-[48px] w-full cursor-pointer flex-row items-center justify-center rounded-xl bg-primary py-2 px-8 font-bold uppercase text-white sm:w-44 vl:hidden"
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
                        <SortingBar
                            sorting={category.products.orderingMode}
                            totalCount={category.products.totalCount}
                        />
                        <CategoryDetailProductsWrapper category={category} containerWrapRef={containerWrapRef} />
                    </div>
                </div>
            </Webline>
        </FilterProvider>
    );
};
