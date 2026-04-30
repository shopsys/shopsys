import { CategoryCard } from 'components/Blocks/Categories/CategoryCard';
import { SkeletonPageCatalog } from 'components/Blocks/Skeleton/SkeletonPageCatalog';
import { Webline } from 'components/Layout/Webline/Webline';
import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useNavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

export const CatalogContent: FC = () => {
    const [{ data: navigationData, fetching: isNavigationFetching }] = useNavigationQuery();
    const { url } = useDomainConfig();
    const [catalogUrl] = getInternationalizedStaticUrls(['/catalog'], url);

    if (isNavigationFetching) {
        return <SkeletonPageCatalog />;
    }

    if (!navigationData) {
        return null;
    }

    const catalogNavigationItem = navigationData.navigation.find(
        (navigationItem) => navigationItem.link === catalogUrl,
    );

    if (!catalogNavigationItem) {
        return null;
    }

    const l1Categories = catalogNavigationItem.categoriesByColumns.flatMap((column) => column.categories);

    const uniqueL1Categories = l1Categories.filter(
        (category, index, self) => index === self.findIndex((c) => c.uuid === category.uuid),
    );

    return (
        <Webline>
            <div className="grid gap-3 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
                {uniqueL1Categories.map((category) => (
                    <CategoryCard key={category.uuid} showChildren category={category} variant="catalog" />
                ))}
            </div>
        </Webline>
    );
};
