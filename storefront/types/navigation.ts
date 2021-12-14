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
