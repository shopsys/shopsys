import { FlagDetailProductsWrapper } from './FlagDetailProductsWrapper';
import { FilteredProductsWrapper } from 'components/Blocks/FilteredProductsWrapper/FilteredProductsWrapper';
import { DeferredFilterPanel } from 'components/Blocks/Product/Filter/DeferredFilterPanel';
import { FilterSelectedParameters } from 'components/Blocks/Product/Filter/FilterSelectedParameters';
import { DeferredFilterAndSortingBar } from 'components/Blocks/SortingBar/DeferredFilterAndSortingBar';
import { Webline } from 'components/Layout/Webline/Webline';
import { TypeFlagDetailFragment } from 'graphql/requests/flags/fragments/FlagDetailFragment.generated';
import { useRef } from 'react';

type FlagDetailContentProps = {
    flag: TypeFlagDetailFragment;
};

export const FlagDetailContent: FC<FlagDetailContentProps> = ({ flag }) => {
    const paginationScrollTargetRef = useRef<HTMLDivElement>(null);

    flag.products.productFilterOptions.flags = null;

    return (
        <Webline>
            <h1>{flag.name}</h1>

            <FilteredProductsWrapper paginationScrollTargetRef={paginationScrollTargetRef}>
                <DeferredFilterPanel
                    defaultOrderingMode={flag.products.defaultOrderingMode}
                    orderingMode={flag.products.orderingMode}
                    originalSlug={flag.slug}
                    productFilterOptions={flag.products.productFilterOptions}
                    slug={flag.slug}
                    totalCount={flag.products.totalCount}
                />

                <div className="flex flex-1 flex-col">
                    <div className="flex flex-col-reverse vl:flex-col">
                        <FilterSelectedParameters filterOptions={flag.products.productFilterOptions} />

                        <DeferredFilterAndSortingBar
                            sorting={flag.products.orderingMode}
                            totalCount={flag.products.totalCount}
                        />
                    </div>

                    <FlagDetailProductsWrapper flag={flag} paginationScrollTargetRef={paginationScrollTargetRef} />
                </div>
            </FilteredProductsWrapper>
        </Webline>
    );
};
