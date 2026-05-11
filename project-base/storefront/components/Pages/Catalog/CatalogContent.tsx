import { CategoryCard } from 'components/Blocks/Categories/CategoryCard';
import { SkeletonPageCatalog } from 'components/Blocks/Skeleton/SkeletonPageCatalog';
import { Webline } from 'components/Layout/Webline/Webline';
import { useCatalogCategoriesQuery } from 'graphql/requests/categories/queries/CatalogCategoriesQuery.generated';

export const CatalogContent: FC = () => {
    const [{ data: catalogCategoriesData, fetching: areCatalogCategoriesFetching }] = useCatalogCategoriesQuery();

    if (areCatalogCategoriesFetching) {
        return <SkeletonPageCatalog />;
    }

    if (!catalogCategoriesData) {
        return null;
    }

    return (
        <Webline>
            <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
                {catalogCategoriesData.categories.map((category) => (
                    <CategoryCard key={category.uuid} showChildren category={category} variant="catalog" />
                ))}
            </div>
        </Webline>
    );
};
