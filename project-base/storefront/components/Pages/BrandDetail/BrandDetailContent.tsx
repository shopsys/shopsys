import { BrandDetailProductsWrapper } from './BrandDetailProductsWrapper';
import { CollapsibleDescriptionWithImage } from 'components/Blocks/CollapsibleDescriptionWithImage/CollapsibleDescriptionWithImage';
import { FilteredProductsWrapper } from 'components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { FilterSelectedParameters } from 'components/Blocks/Product/Filter/FilterSelectedParameters';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeBrandDetailFragment } from 'graphql/requests/brands/fragments/BrandDetailFragment.generated';
import { useRef } from 'react';
import { useCurrentPageQuery } from 'utils/queryParams/useCurrentPageQuery';

type BrandDetailContentProps = {
    brand: TypeBrandDetailFragment;
};

export const BrandDetailContent: FC<BrandDetailContentProps> = ({ brand }) => {
    const scrollTargetRef = useRef<HTMLDivElement>(null);
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);
    const currentPage = useCurrentPageQuery();

    brand.products.productFilterOptions.brands = null;

    return (
        <Webline>
            <h1>{brand.seoH1 || brand.name}</h1>

            <CollapsibleDescriptionWithImage
                currentPage={currentPage}
                description={brand.description}
                imageName={brand.mainImage?.name || brand.name}
                imageUrl={brand.mainImage?.url}
                scrollTargetRef={scrollTargetRef}
            />

            <FilteredProductsWrapper ref={paginationScrollTargetRef}>
                <DeferredFilterPanel
                    defaultOrderingMode={brand.products.defaultOrderingMode}
                    orderingMode={brand.products.orderingMode}
                    originalSlug={brand.slug}
                    productFilterOptions={brand.products.productFilterOptions}
                    slug={brand.slug}
                    totalCount={brand.products.totalCount}
                />

                <div className="flex flex-1 flex-col">
                    <div className="flex flex-col">
                        <FilterSelectedParameters filterOptions={brand.products.productFilterOptions} />

                        <DeferredFilterAndSortingBar
                            sorting={brand.products.orderingMode}
                            totalCount={brand.products.totalCount}
                        />
                    </div>

                    <BrandDetailProductsWrapper brand={brand} paginationScrollTargetRef={paginationScrollTargetRef} />
                </div>
            </FilteredProductsWrapper>
        </Webline>
    );
};
