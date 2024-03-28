import { SimpleNavigation } from 'components/Blocks/SimpleNavigation/SimpleNavigation';
import { usePromotedCategoriesQuery } from 'graphql/requests/categories/queries/PromotedCategoriesQuery.generated';

// import dynamic from 'next/dynamic';

// const SkeletonModulePromotedCategories = dynamic(
//     () =>
//         import('components/Blocks/Skeleton/SkeletonModulePromotedCategories').then(
//             (component) => component.SkeletonModulePromotedCategories,
//         ),
//     { ssr: false },
// );

export const PromotedCategories: FC = () => {
    const [{ data: promotedCategoriesData }] = usePromotedCategoriesQuery();

    // if (fetching) {
    //     return <SkeletonModulePromotedCategories />;
    // }

    if (!promotedCategoriesData) {
        return null;
    }

    return <SimpleNavigation listedItems={promotedCategoriesData.promotedCategories} />;
};
