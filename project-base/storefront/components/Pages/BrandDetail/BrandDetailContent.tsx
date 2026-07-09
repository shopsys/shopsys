import { CollapsibleDescriptionWithImage } from 'components/Blocks/CollapsibleDescriptionWithImage/CollapsibleDescriptionWithImage';
import { FilteredProductsWrapper } from 'components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { DeferredFilterSelectedParameters } from 'components/Blocks/Product/Filter/DeferredFilterSelectedParameters';
import { DeferredLastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/DeferredLastVisitedProducts';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { TypeBrandDetailFragment } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { useRef } from 'react';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';
import { BrandDetailProductsWrapper } from './BrandDetailProductsWrapper';

type BrandDetailContentProps = {
    brand: TypeBrandDetailFragment;
};

export const BrandDetailContent: FC<BrandDetailContentProps> = ({ brand }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const currentPage = useCurrentPageQuery();

    const title = useSeoTitleWithPagination(brand.products.totalCount, brand.name, brand.seoH1);

    const productFilterOptions = { ...brand.products.productFilterOptions, brands: null };

    return (
        <VerticalStack gap="md">
            <CollapsibleDescriptionWithImage
                currentPage={currentPage}
                description={brand.description}
                imageName={brand.mainImage?.name || brand.name}
                imageUrl={brand.mainImage?.url}
                title={title}
            />

            <FilteredProductsWrapper>
                <DeferredFilterPanel
                    defaultOrderingMode={brand.products.defaultOrderingMode}
                    orderingMode={brand.products.orderingMode}
                    originalSlug={brand.slug}
                    productFilterOptions={productFilterOptions}
                    slug={brand.slug}
                    totalCount={brand.products.totalCount}
                />

                <div
                    className="flex flex-1 scroll-mt-5 flex-col gap-5"
                    id="product-list"
                    ref={paginationScrollTargetRef}
                    tabIndex={-1}
                >
                    <div className="flex vl:flex-col flex-col-reverse">
                        <DeferredFilterSelectedParameters filterOptions={productFilterOptions} />

                        <DeferredFilterAndSortingBar
                            sorting={brand.products.orderingMode}
                            totalCount={brand.products.totalCount}
                        />
                    </div>

                    <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                        <BrandDetailProductsWrapper brand={brand} />
                    </PaginationProvider>
                </div>
            </FilteredProductsWrapper>

            <DeferredLastVisitedProducts />
        </VerticalStack>
    );
};
