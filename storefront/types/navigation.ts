import { ImageType } from './image';
import { NavigationSubCategoriesLinkFragmentApi } from 'graphql/generated';

export type NavigationCategory = {
    name: string;
    slug: string;
    image: ImageType;
    children: NavigationSubCategoriesLinkFragmentApi['children'];
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
