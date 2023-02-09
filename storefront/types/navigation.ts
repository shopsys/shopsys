import { ImageSizesFragmentApi, NavigationSubCategoriesLinkFragmentApi } from 'graphql/generated';

export type NavigationCategory = {
    name: string;
    slug: string;
    images: ImageSizesFragmentApi[];
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
