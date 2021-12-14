import { BreadcrumbItemType } from 'types/breadcrumb';
import { ImageType } from 'components/Basic/Image/types';
import { ListedProductEdgesType } from 'components/Blocks/Product/types';

export type SimpleCategoryType = {
    __typename?: 'Category';
    name: string;
    slug: string;
};

export type ListedCategoryType = {
    uuid: string;
    name: string;
    slug: string;
    image: ImageType | null;
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
    seoH1: string | null;
    children: ListedCategoryType[];
    products: ListedProductEdgesType;
    readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
    linkedCategories: ListedCategoryType[];
};
