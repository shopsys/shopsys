import { BreadcrumbItemType } from 'types/breadcrumb';
import { ImageSizeType } from 'types/image';
import { ListedProductEdgesType } from 'types/product';

export type SimpleCategoryType = {
    __typename?: 'Category';
    name: string;
    slug: string;
};

export type ListedCategoryType = {
    uuid: string;
    name: string;
    slug: string;
    image: ImageSizeType | null;
    totalCount?: number;
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
    products: ListedProductEdgesType;
    readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
    linkedCategories: ListedCategoryType[];
};
