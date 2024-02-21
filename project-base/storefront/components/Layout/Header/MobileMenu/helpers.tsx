import { MenuItem } from './MobileMenuContent';
import { NavigationQueryApi } from 'graphql/generated';

type NavigationColumnCategories = NavigationQueryApi['navigation'][number]['categoriesByColumns'];

export const mapNavigationMenuItems = (navigationItems: NavigationQueryApi['navigation']) =>
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
