import { DeferredCategoryDetailProductsWrapper } from './CategoryDetailProductsWrapper/DeferredCategoryDetailProductsWrapper';
import { CollapsibleDescriptionWithImage } from 'components/Blocks/CollapsibleDescriptionWithImage/CollapsibleDescriptionWithImage';
import { FilteredProductsWrapper } from 'components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { FilterSelectedParameters } from 'components/Blocks/Product/Filter/FilterSelectedParameters';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import useTranslation from 'next-translate/useTranslation';
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
    const { t } = useTranslation();
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const currentPage = useCurrentPageQuery();

    const title = useSeoTitleWithPagination(category.products.totalCount, category.name, category.seoH1);

    const pageViewEvent = useGtmFriendlyPageViewEvent(category);
    useGtmPageViewEvent(pageViewEvent, isFetchingVisible);

    return (
        <VerticalStack gap="md">
            <CollapsibleDescriptionWithImage
                currentPage={currentPage}
                description={category.description}
                imageName={category.images[0]?.name || category.name}
                imageUrl={category.images[0]?.url}
                title={title}
            />

            <SimpleNavigation
                isWithoutSlider
                linkTypeOverride="category"
                listedItems={category.children}
                title={t('Subcategories')}
            />

            <FilteredProductsWrapper>
                <DeferredFilterPanel
                    categoryAutomatedFilters={category.automatedFilters}
                    defaultOrderingMode={category.products.defaultOrderingMode}
                    orderingMode={category.products.orderingMode}
                    originalSlug={category.originalCategorySlug}
                    productFilterOptions={category.products.productFilterOptions}
                    slug={category.slug}
                    totalCount={category.products.totalCount}
                />

                <div className="flex flex-1 flex-col gap-5" id="product-list">
                    {!!category.bestsellers.length && <CategoryBestsellers products={category.bestsellers} />}

                    <div className="vl:flex-col flex scroll-mt-5 flex-col-reverse" ref={paginationScrollTargetRef}>
                        <FilterSelectedParameters filterOptions={category.products.productFilterOptions} />

                        <DeferredFilterAndSortingBar
                            sorting={category.products.orderingMode}
                            totalCount={category.products.totalCount}
                        />
                    </div>

                    <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                        <DeferredCategoryDetailProductsWrapper category={category} />
                    </PaginationProvider>
                </div>
            </FilteredProductsWrapper>

            {!!category.readyCategorySeoMixLinks.length && (
                <AdvancedSeoCategories readyCategorySeoMixLinks={category.readyCategorySeoMixLinks} />
            )}

            <DeferredLastVisitedProducts />
        </VerticalStack>
    );
};
