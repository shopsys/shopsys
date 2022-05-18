import { getFirstImage } from 'connectors/image/Image';
import {
    CategoriesByColumnFragmentApi,
    ColumnCategoriesFragmentApi,
    NavigationQueryApi,
    NavigationSubCategoriesLinkFragmentApi,
    useNavigationQueryApi,
} from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/UseQueryError';
import {
    NavigationCategoriesColumn,
    NavigationCategory,
    NavigationItem,
    NavigationSubCategory,
} from 'types/navigation';

export function useNavigationItems(): NavigationItem[] {
    const [{ data, error }] = useNavigationQueryApi();
    useQueryError(error);

    if (data?.navigation !== undefined) {
        return mapNavigation(data.navigation);
    }
    return [];
}

function mapNavigation(data: NavigationQueryApi['navigation']): NavigationItem[] {
    const mappedNavigation = [];

    for (const navigationItem of data) {
        mappedNavigation.push({
            ...navigationItem,
            categoriesByColumns: mapNavigationCategoriesByColumns(navigationItem.categoriesByColumns),
        });
    }
    return mappedNavigation;
}

function mapNavigationCategoriesByColumns(
    categoriesByColumns: CategoriesByColumnFragmentApi['categoriesByColumns'],
): NavigationCategoriesColumn[] {
    const mappedCategoriesByColumns = [];
    for (const categoriesByColumn of categoriesByColumns) {
        mappedCategoriesByColumns.push({
            ...categoriesByColumn,
            categories: mapCategories(categoriesByColumn.categories),
        });
    }

    return mappedCategoriesByColumns;
}

const mapSubCategories = (apiData: NavigationSubCategoriesLinkFragmentApi['children']): NavigationSubCategory[] => {
    return apiData.map((subCategory) => {
        return {
            name: subCategory.name,
            slug: subCategory.slug,
        };
    });
};

const mapCategories = (data: ColumnCategoriesFragmentApi['categories']): NavigationCategory[] => {
    const mappedCategories = [];
    for (const category of data) {
        if (!(0 in category.images)) {
            continue;
        }
        const mappedImage = getFirstImage(category.images);
        if (mappedImage === null) {
            continue;
        }

        mappedCategories.push({
            name: category.name,
            slug: category.slug,
            children: mapSubCategories(category.children),
            image: mappedImage,
        });
    }
    return mappedCategories;
};
