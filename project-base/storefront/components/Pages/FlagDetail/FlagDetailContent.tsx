import { FlagDetailProductsWrapper } from './FlagDetailProductsWrapper';
import { FilteredProductsWrapper } from 'components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { FilterSelectedParameters } from 'components/Blocks/Product/Filter/FilterSelectedParameters';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { VerticalStack } from 'components/Layout/VerticalStack/VerticalStack';
import { Webline } from 'components/Layout/Webline/Webline';
import { PaginationProvider } from 'components/providers/PaginationProvider';
import { TypeFlagDetailFragment } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { useRef } from 'react';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';

type FlagDetailContentProps = {
    flag: TypeFlagDetailFragment;
};

export const FlagDetailContent: FC<FlagDetailContentProps> = ({ flag }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    const title = useSeoTitleWithPagination(flag.products.totalCount, flag.name);

    flag.products.productFilterOptions.flags = null;

    return (
        <VerticalStack gap="md">
            <Webline>
                <h1>{title}</h1>
            </Webline>

            <FilteredProductsWrapper>
                <DeferredFilterPanel
                    defaultOrderingMode={flag.products.defaultOrderingMode}
                    orderingMode={flag.products.orderingMode}
                    originalSlug={flag.slug}
                    productFilterOptions={flag.products.productFilterOptions}
                    slug={flag.slug}
                    totalCount={flag.products.totalCount}
                />

                <div
                    className="flex flex-1 scroll-mt-5 flex-col gap-5"
                    id="product-list"
                    ref={paginationScrollTargetRef}
                >
                    <div className="vl:flex-col flex flex-col-reverse">
                        <FilterSelectedParameters filterOptions={flag.products.productFilterOptions} />

                        <DeferredFilterAndSortingBar
                            sorting={flag.products.orderingMode}
                            totalCount={flag.products.totalCount}
                        />
                    </div>

                    <PaginationProvider paginationScrollTargetRef={paginationScrollTargetRef}>
                        <FlagDetailProductsWrapper flag={flag} />
                    </PaginationProvider>
                </div>
            </FilteredProductsWrapper>

            <LastVisitedProducts />
        </VerticalStack>
    );
};
