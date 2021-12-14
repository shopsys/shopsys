import { BreadcrumbItemType } from 'connectors/breadcrumb/Breadcrumb';
import { ListedCategoryType } from 'connectors/categories/types';
import { ListedProductEdgesType } from 'components/Blocks/Product/types';

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
