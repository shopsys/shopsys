import { DeferredCategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper/DeferredCategoryDetailProductsWrapper';
import { CollapsibleText } from 'components/Basic/CollapsibleText/CollapsibleText';
import { Image } from 'components/Basic/Image/Image';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { FilterSelectedParameters } from 'components/Blocks/Product/Filter/FilterSelectedParameters';
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

const AdvancedSeoCategories = dynamic(
    () => import('./AdvancedSeoCategories').then((component) => component.AdvancedSeoCategories),
    { suspense: true },
);

const CategoryBestsellers = dynamic(
    () => import('./CategoryBestsellers/CategoryBestsellers').then((component) => component.CategoryBestsellers),
    { suspense: true },
);

type CategoryDetailContentProps = {
    category: TypeCategoryDetailFragment;
    isFetchingVisible: boolean;
};

export const CategoryDetailContent: FC<CategoryDetailContentProps> = ({ category, isFetchingVisible }) => {
    const [isPanelOpen, setIsPanelOpen] = useState(false);
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const currentPage = useCurrentPageQuery();
    const scrollTargetRef = useRef<HTMLDivElement>(null);

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
            <h1 ref={scrollTargetRef}>{title}</h1>
            <div className="mb-7 flex flex-col-reverse justify-between gap-5 lg:flex-row">
                {!!category.description && currentPage === 1 && (
                    <CollapsibleText scrollTargetRef={scrollTargetRef} text={category.description} />
                )}
                <Image
                    alt={category.name}
                    className="rounden-lg h-full w-full rounded-lg md:max-w-80"
                    height={150}
                    src={category.images[0]?.url}
                    width={300}
                />
            </div>

            <SimpleNavigation
                isWithoutSlider
                className="my-7"
                linkTypeOverride="category"
                listedItems={[...category.children, ...category.linkedCategories]}
            />

            <div
                className="mb-8 flex scroll-mt-5 flex-col vl:mb-10 vl:flex-row vl:flex-wrap vl:gap-4"
                ref={paginationScrollTargetRef}
            >
                <div
                    className={twJoin(
                        'fixed bottom-0 left-0 right-10 top-0 max-w-[400px] -translate-x-full overflow-hidden transition max-vl:z-aboveOverlay vl:static vl:w-[227px] vl:translate-x-0 vl:rounded-none vl:transition-none',
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
                    {!!category.bestsellers.length && <CategoryBestsellers products={category.bestsellers} />}

                    <div className="flex flex-col">
                        <FilterSelectedParameters filterOptions={category.products.productFilterOptions} />

                        <DeferredFilterAndSortingBar
                            handlePanelOpenerClick={handlePanelOpenerClick}
                            sorting={category.products.orderingMode}
                            totalCount={category.products.totalCount}
                        />
                    </div>

                    <DeferredCategoryDetailProductsWrapper
                        category={category}
                        paginationScrollTargetRef={paginationScrollTargetRef}
                    />
                </div>
            </div>
            {!!category.readyCategorySeoMixLinks.length && (
                <AdvancedSeoCategories readyCategorySeoMixLinks={category.readyCategorySeoMixLinks} />
            )}
        </Webline>
    );
};
