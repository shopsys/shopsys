import { useFetchQuery } from '../../hooks/UseFetchQuery';

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
                        images(size: "default") {
                            url
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

type NavigationSubCategory = {
    name: string;
    slug: string;
};

type NavigationCategory = {
    name: string;
    slug: string;
    image: {
        url: string;
        width: number;
        height: number;
    };
    children: Array<NavigationSubCategory>;
};

type NavigationItem = {
    name: string;
    link: string;
    categoriesByColumns: Array<{
        columnNumber: number;
        categories: Array<NavigationCategory>;
    }>;
};

type NavigationItemApiData = {
    name: string;
    link: string;
    categoriesByColumns: Array<{
        columnNumber: number;
        categories: Array<{
            name: string;
            slug: string;
            images: Array<{
                url: string;
                width: number;
                height: number;
            }>;
            children: Array<{
                name: string;
                slug: string;
            }>;
        }>;
    }>;
};

function mapCategories(data) {
    const mappedCategories = [];
    for (const category of data) {
        mappedCategories.push({
            ...category,
            image: category.images[0],
        });
    }
    return mappedCategories;
}

function mapNavigationCategoriesByColumns(categoriesByColumns) {
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
