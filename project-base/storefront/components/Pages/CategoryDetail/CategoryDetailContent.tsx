import { AdvancedSeoCategories } from './AdvancedSeoCategories/AdvancedSeoCategories';
import { CategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper';
import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { HeadingPaginated } from 'components/Basic/Heading/HeadingPaginated';
import { Icon } from 'components/Basic/Icon/Icon';
import { Overlay } from 'components/Basic/Overlay/Overlay';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { FilterPanel } from 'components/Blocks/Product/Filter/FilterPanel';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { SortingBar } from 'components/Blocks/SortingBar/SortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { CategoryDetailFragmentApi } from 'graphql/generated';
import { PAGE_QUERY_PARAMETER_NAME } from 'helpers/queryParams/queryParamNames';
import { useTypedTranslationFunction } from 'hooks/typescript/useTypedTranslationFunction';
import { useRouter } from 'next/router';
import { useCallback, useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import Skeleton from 'react-loading-skeleton';

type CategoryDetailContentProps = {
    category: CategoryDetailFragmentApi;
    showTitleAndDescriptionSkeleton: boolean;
};

export const CategoryDetailContent: FC<CategoryDetailContentProps> = ({
    category,
    showTitleAndDescriptionSkeleton,
}) => {
    const t = useTypedTranslationFunction();
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const containerWrapRef = useRef<null | HTMLDivElement>(null);
    const { query } = useRouter();
    const isFiltered = 'filter' in query;

    const handlePanelOpenerClick = useCallback(() => {
        const body = document.getElementsByTagName('body')[0];

        setIsPanelOpen((prev) => {
            const newValue = !prev;
            body.style.overflow = newValue ? 'hidden' : 'visible';

            return newValue;
        });
    }, []);

    return (
        <Webline>
            {isFiltered && <MetaRobots content="noindex, follow" />}
            <div className="mb-7 flex flex-col vl:mb-10 vl:flex-row vl:flex-wrap" ref={containerWrapRef}>
                <div
                    className={twJoin(
                        'fixed top-0 left-0 bottom-0 right-10 max-w-[400px] -translate-x-full transition max-vl:z-aboveOverlay vl:static vl:w-[304px] vl:translate-x-0 vl:transition-none',
                        isPanelOpen && 'translate-x-0',
                    )}
                >
                    <FilterPanel
                        productFilterOptions={category.products.productFilterOptions}
                        orderingMode={category.products.orderingMode}
                        defaultOrderingMode={category.products.defaultOrderingMode}
                        originalSlug={category.originalCategorySlug}
                        panelCloseHandler={handlePanelOpenerClick}
                        slug={category.slug}
                        totalCount={category.products.totalCount}
                    />
                </div>
                <Overlay isActive={isPanelOpen} onClick={handlePanelOpenerClick} />
                <div className="flex flex-1 flex-col overflow-hidden vl:pl-12">
                    <Adverts positionName="productList" className="mb-5" />
                    <>
                        {showTitleAndDescriptionSkeleton ? (
                            <div className="mb-12 flex w-full flex-col gap-4 ">
                                <Skeleton className="h-9 w-5/6" />
                                <Skeleton count={4} className="mb-3 h-4" />
                            </div>
                        ) : (
                            <>
                                <HeadingPaginated type="h1" totalCount={category.products.totalCount}>
                                    {category.seoH1 !== null ? category.seoH1 : category.name}
                                </HeadingPaginated>
                                {category.description !== null &&
                                    category.description !== '' &&
                                    (query[PAGE_QUERY_PARAMETER_NAME] ?? 1) === 1 && (
                                        <div
                                            dangerouslySetInnerHTML={{ __html: category.description }}
                                            className="mb-4"
                                        />
                                    )}
                            </>
                        )}
                    </>

                    <Adverts positionName="productListMiddle" currentCategory={category} className="mb-7" />
                    <SimpleNavigation
                        listedItems={[...category.children, ...category.linkedCategories]}
                        className="mb-6"
                    />
                    <AdvancedSeoCategories readyCategorySeoMixLinks={category.readyCategorySeoMixLinks} />
                    <div className="flex flex-col gap-3 sm:flex-row">
                        <div
                            className="relative flex w-full cursor-pointer flex-row items-center justify-center rounded-xl bg-primary py-3 px-8 font-bold uppercase text-white vl:mb-3 vl:hidden"
                            onClick={handlePanelOpenerClick}
                        >
                            <Icon iconType="icon" icon="Filter" className="mr-3 w-6 font-bold text-white" />
                            {t('Filter')}
                        </div>
                        <SortingBar
                            sorting={category.products.orderingMode}
                            totalCount={category.products.totalCount}
                        />
                    </div>
                    <CategoryDetailProductsWrapper category={category} containerWrapRef={containerWrapRef} />
                </div>
            </div>
        </Webline>
    );
};
