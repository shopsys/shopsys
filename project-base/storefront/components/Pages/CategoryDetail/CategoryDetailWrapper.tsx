import { CategoryDetailContent } from './CategoryDetailContent';
import { MetaRobots } from 'components/Basic/Head/MetaRobots';
import { LastVisitedProducts } from 'components/Blocks/Product/LastVisitedProducts/LastVisitedProducts';
import { CommonLayout } from 'components/Layout/CommonLayout';
import { TypeCategoryDetailFragment } from 'graphql/requests/categories/fragments/CategoryDetailFragment.generated';
import { useGtmFriendlyPageViewEvent } from 'gtm/factories/useGtmFriendlyPageViewEvent';
import { useGtmPageViewEvent } from 'gtm/utils/pageViewEvents/useGtmPageViewEvent';
import { FilterOptionsUrlQueryType } from 'types/productFilter';
import { useSeoTitleWithPagination } from 'utils/seo/useSeoTitleWithPagination';

export const CategoryDetailWrapper: FC<{
    categoryData: TypeCategoryDetailFragment | null | undefined;
    currentFilter: FilterOptionsUrlQueryType | null;
    isFetchingVisible: boolean;
}> = ({ categoryData, currentFilter, isFetchingVisible }) => {
    const seoTitle = useSeoTitleWithPagination(
        categoryData?.products.totalCount,
        categoryData?.name,
        categoryData?.seoTitle,
    );

    const pageViewEvent = useGtmFriendlyPageViewEvent(categoryData);
    useGtmPageViewEvent(pageViewEvent, isFetchingVisible);

    return (
        <>
            {!!currentFilter && <MetaRobots content="noindex, follow" />}

            <CommonLayout
                breadcrumbs={categoryData?.breadcrumb}
                breadcrumbsType="category"
                description={categoryData?.seoMetaDescription}
                hreflangLinks={categoryData?.hreflangLinks}
                isFetchingData={isFetchingVisible}
                title={seoTitle}
            >
                {!!categoryData && <CategoryDetailContent category={categoryData} />}
                <LastVisitedProducts />
            </CommonLayout>
        </>
    );
};
