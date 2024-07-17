import { DeferredCategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper/DeferredCategoryDetailProductsWrapper';
import { Adverts } from 'components/Blocks/Adverts/Adverts';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import dynamic from 'next/dynamic';
import { useRef, useState } from 'react';
import { twJoin } from 'tailwind-merge';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';

const Overlay = dynamic(() => import('components/Basic/Overlay/Overlay').then((component) => component.Overlay), {
    ssr: false,
});

const AdvancedSeoCategories = dynamic(() =>
    import('./AdvancedSeoCategories').then((component) => component.AdvancedSeoCategories),
);

const CategoryBestsellers = dynamic(() =>
    import('./CategoryBestsellers/CategoryBestsellers').then((component) => component.CategoryBestsellers),
);

type CategoryDetailContentProps = {
    category: TypeCategoryDetailFragment;
    isFetchingVisible: boolean;
};

export const CategoryDetailContent: FC<CategoryDetailContentProps> = ({ category, isFetchingVisible }) => {
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const currentPage = useCurrentPageQuery();

    const title = useSeoTitleWithPagination(category.products.totalCount, category.name, category.seoH1);

    const handlePanelOpenerClick = () => {
        const body = document.getElementsByTagName('body')[0];

        setIsPanelOpen((prev) => {
            const newValue = !prev;
            body.style.overflow = newValue ? 'hidden' : 'visible';

            return newValue;
        });
    };

    const pageViewEvent = useGtmFriendlyPageViewEvent(category);
    useGtmPageViewEvent(pageViewEvent, isFetchingVisible);

    return (
        <Webline>
            <div
                className="mb-7 flex scroll-mt-5 flex-col vl:mb-10 vl:flex-row vl:flex-wrap vl:gap-12"
                ref={paginationScrollTargetRef}
            >
                <div
                    className={twJoin(
                        'fixed top-0 left-0 bottom-0 right-10 max-w-[400px] -translate-x-full transition max-vl:z-aboveOverlay vl:static vl:w-[304px] vl:translate-x-0 vl:transition-none',
                        isPanelOpen && 'translate-x-0',
                    )}
                >
                    <DeferredFilterPanel
                        defaultOrderingMode={category.products.defaultOrderingMode}
                        orderingMode={category.products.orderingMode}
                        originalSlug={category.originalCategorySlug}
                        panelCloseHandler={handlePanelOpenerClick}
                        productFilterOptions={category.products.productFilterOptions}
                        slug={category.slug}
                        totalCount={category.products.totalCount}
                    />
                </div>

                {isPanelOpen && <Overlay isActive={isPanelOpen} onClick={handlePanelOpenerClick} />}

                <div className="flex flex-1 flex-col">
                    <Adverts className="mt-6" currentCategory={category} positionName="productList" />

                    <h1>{title}</h1>

                    {!!category.description && currentPage === 1 && (
                        <div dangerouslySetInnerHTML={{ __html: category.description }} />
                    )}

                    <Adverts className="mt-6" currentCategory={category} positionName="productListMiddle" />

                    <SimpleNavigation
                        className="mt-6"
                        linkTypeOverride="category"
                        listedItems={[...category.children, ...category.linkedCategories]}
                    />

                    {!!category.readyCategorySeoMixLinks.length && (
                        <AdvancedSeoCategories readyCategorySeoMixLinks={category.readyCategorySeoMixLinks} />
                    )}

                    {!!category.bestsellers.length && <CategoryBestsellers products={category.bestsellers} />}

                    <DeferredFilterAndSortingBar
                        handlePanelOpenerClick={handlePanelOpenerClick}
                        sorting={category.products.orderingMode}
                        totalCount={category.products.totalCount}
                    />

                    <DeferredCategoryDetailProductsWrapper
                        category={category}
                        paginationScrollTargetRef={paginationScrollTargetRef}
                    />
                </div>
            </div>
        </Webline>
    );
};
