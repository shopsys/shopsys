import { DeferredCategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper/DeferredCategoryDetailProductsWrapper';
import { CollapsibleDescriptionWithImage } from 'components/Blocks/CollapsibleDescriptionWithImage/CollapsibleDescriptionWithImage';
import { FilteredProductsWrapper } from 'components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { FilterSelectedParameters } from 'components/Blocks/Product/Filter/FilterSelectedParameters';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import dynamic from 'next/dynamic';
import { useRef } from 'react';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';

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
    const scrollTargetRef = useRef<HTMLDivElement>(null);
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const currentPage = useCurrentPageQuery();

    const title = useSeoTitleWithPagination(category.products.totalCount, category.name, category.seoH1);

    const pageViewEvent = useGtmFriendlyPageViewEvent(category);
    useGtmPageViewEvent(pageViewEvent, isFetchingVisible);

    return (
        <Webline>
            <h1 ref={scrollTargetRef}>{title}</h1>

            <CollapsibleDescriptionWithImage
                currentPage={currentPage}
                description={category.description}
                imageName={category.images[0].name || category.name}
                imageUrl={category.images[0].url}
                scrollTargetRef={scrollTargetRef}
            />

            <SimpleNavigation
                isWithoutSlider
                className="my-7"
                linkTypeOverride="category"
                listedItems={[...category.children, ...category.linkedCategories]}
            />

            <FilteredProductsWrapper paginationScrollTargetRef={paginationScrollTargetRef}>
                <DeferredFilterPanel
                    defaultOrderingMode={category.products.defaultOrderingMode}
                    orderingMode={category.products.orderingMode}
                    originalSlug={category.originalCategorySlug}
                    productFilterOptions={category.products.productFilterOptions}
                    slug={category.slug}
                    totalCount={category.products.totalCount}
                />

                <div className="flex flex-1 flex-col">
                    {!!category.bestsellers.length && <CategoryBestsellers products={category.bestsellers} />}

                    <div className="flex flex-col-reverse vl:flex-col">
                        <FilterSelectedParameters filterOptions={category.products.productFilterOptions} />

                        <DeferredFilterAndSortingBar
                            sorting={category.products.orderingMode}
                            totalCount={category.products.totalCount}
                        />
                    </div>

                    <DeferredCategoryDetailProductsWrapper
                        category={category}
                        paginationScrollTargetRef={paginationScrollTargetRef}
                    />
                </div>
            </FilteredProductsWrapper>

            {!!category.readyCategorySeoMixLinks.length && (
                <AdvancedSeoCategories readyCategorySeoMixLinks={category.readyCategorySeoMixLinks} />
            )}
        </Webline>
    );
};
