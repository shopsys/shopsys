import { FilterPanel } from 'app/_components/Blocks/Product/Filter/FilterPanel';
import { getCategoryDetailQuery } from 'app/_queries/getCategoryDetailQuery';
import { TypeProductFilter, TypeProductOrderingModeEnum } from 'graphql/types';
import { twJoin } from 'tailwind-merge';

type FilterPanelWrapperProps = {
    params: Promise<{
        categorySlug: string;
        sort: TypeProductOrderingModeEnum;
        filter: TypeProductFilter;
        page: number;
    }>;
};

export const FilterPanelWrapper: FC<FilterPanelWrapperProps> = async ({ params }) => {
    const { categorySlug, sort, filter } = await params;

    const categoryData = await getCategoryDetailQuery(categorySlug, sort, filter);

    if (!categoryData) {
        return null;
    }

    // const { isFilterPanelOpen, setIsFilterPanelOpen } = useSessionStore((s) => ({
    //     isFilterPanelOpen: s.isFilterPanelOpen,
    //     setIsFilterPanelOpen: s.setIsFilterPanelOpen,
    // }));

    return (
        <>
            <div
                className={twJoin(
                    'max-vl:z-aboveOverlay vl:static vl:w-[227px] vl:translate-x-0 vl:rounded-none vl:transition-none fixed top-0 right-10 bottom-0 left-0 max-w-[400px] -translate-x-full overflow-hidden transition',
                    // isFilterPanelOpen && 'translate-x-0',
                )}
            >
                <FilterPanel
                    categoryAutomatedFilters={categoryData.automatedFilters}
                    currentFilter={filter}
                    // defaultOrderingMode={categoryData.products.defaultOrderingMode}
                    // orderingMode={categoryData.products.orderingMode}
                    // originalSlug={categoryData.originalCategorySlug}
                    productFilterOptions={categoryData.products.productFilterOptions}
                    // slug={categoryData.slug}
                    totalCount={categoryData.products.totalCount}
                />
            </div>

            {/* {isFilterPanelOpen && <Overlay isActive={isFilterPanelOpen} onClick={() => setIsFilterPanelOpen(false)} />} */}
        </>
    );
};
