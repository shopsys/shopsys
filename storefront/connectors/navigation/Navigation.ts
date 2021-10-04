import { ImageApiType } from 'components/Basic/Image/types';
import { useFetchQuery } from 'hooks/graphQl/UseFetchQuery';

export const navigationQuery = `
        query navigation {
            navigation {
                name
                link
                categoriesByColumns{
                    columnNumber
                    categories{
                        name
                        slug
                        images(sizes: "default") {
                            sizes {
                                url
                            }
                        }
                        children{
                            name
                            slug
                        }
                    }
                }
            }
        }
    ` as const;

export type NavigationSubCategory = {
    name: string;
    slug: string;
};

export type NavigationCategory = {
    name: string;
    slug: string;
    image: {
        url: string;
        width: number;
        height: number;
    };
    children: NavigationSubCategory[];
};

export type NavigationCategoriesColumn = {
    columnNumber: number;
    categories: NavigationCategory[];
};

export type NavigationItem = {
    name: string;
    link: string;
    categoriesByColumns: NavigationCategoriesColumn[];
};

type NavigationCategoryApiData = {
    name: string;
    slug: string;
    images: ImageApiType[];
    children: {
        name: string;
        slug: string;
    }[];
};

type NavigationCategoriesColumnApiData = {
    columnNumber: number;
    categories: NavigationCategoryApiData[];
};

type NavigationItemApiData = {
    name: string;
    link: string;
    categoriesByColumns: NavigationCategoriesColumnApiData[];
};

function mapCategories(data: NavigationCategoryApiData[]): NavigationCategory[] {
    const mappedCategories = [];
    for (const category of data) {
        mappedCategories.push({
            ...category,
            image: category.images[0].sizes[0],
        });
    }
    return mappedCategories;
}

function mapNavigationCategoriesByColumns(
    categoriesByColumns: NavigationCategoriesColumnApiData[],
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

function mapNavigation(data: NavigationItemApiData[]): NavigationItem[] {
    const mappedNavigation = [];

    for (const navigationItem of data) {
        mappedNavigation.push({
            ...navigationItem,
            categoriesByColumns: mapNavigationCategoriesByColumns(navigationItem.categoriesByColumns),
        });
    }
    return mappedNavigation;
}

export function getNavigationItems(): NavigationItem[] {
    const result = useFetchQuery({ query: navigationQuery });
    const navigationApiData = result?.data?.navigation;

    if (navigationApiData !== undefined) {
        return mapNavigation(navigationApiData);
    }
    return [];
}
