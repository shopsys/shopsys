import { MenuItem } from './MobileMenuContent';
import { NavigationQuery } from 'graphql/requests/navigation/queries/NavigationQuery.generated';

type NavigationColumnCategories = NavigationQuery['navigation'][number]['categoriesByColumns'];

export const mapNavigationMenuItems = (navigationItems: NavigationQuery['navigation']) =>
    navigationItems.map(({ name: baseItemName, link, categoriesByColumns }) => ({
        link,
        name: baseItemName,
        children: mapCategoriesChildren(categoriesByColumns, baseItemName),
    }));

const mapCategoriesChildren = (categoriesByColumns: NavigationColumnCategories, parentItem: string) =>
    categoriesByColumns.reduce<MenuItem[]>(
        (accumulator, { categories }) => [
            ...accumulator,
            ...categories.map(({ name, slug, children }) => ({
                name,
                link: slug,
                parentItem,
                children: children.map((child) => ({ name: child.name, link: child.slug, parentItem: name })),
            })),
        ],
        [],
    );
