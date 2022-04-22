import { BreadcrumbItemType } from 'types/breadcrumb';
import { ImageType } from 'types/image';
import { ListedProductConnectionType } from 'types/product';

export type SimpleCategoryType = {
    __typename?: 'Category';
    name: string;
    slug: string;
};

export type SimpleCategoryConnectionType = {
    totalCount: number;
    categories: SimpleCategoryType[];
};

export type ListedCategoryType = {
    uuid: string;
    name: string;
    slug: string;
    image: ImageType | null;
    totalCount?: number;
};

export type ListedCategoryConnectionType = {
    totalCount: number;
    categories: ListedCategoryType[];
};

export type ReadyCategorySeoMixLink = {
    name: string;
    slug: string;
};

export type CategoryDetailType = {
    breadcrumb: BreadcrumbItemType[];
    __typename: 'Category';
    uuid: string;
    name: string;
    slug: string;
    originalCategorySlug: string | null;
    seoH1: string | null;
    children: ListedCategoryType[];
    productConnection: ListedProductConnectionType;
    readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
    linkedCategories: ListedCategoryType[];
};
