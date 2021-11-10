import { BreadcrumbType } from 'connectors/breadcrumb/Breadcrumb';
import { CategoryItemType } from 'components/Blocks/Categories/CategoryItem/types';
import { ListedProductEdgesType } from 'components/Blocks/Product/types';
import { SlugType } from 'connectors/slug/Slug';

export type ReadyCategorySeoMixLink = {
    name: string;
    slug: string;
};

export interface CategoryDetailType extends SlugType, BreadcrumbType {
    __typename: 'Category';
    uuid: string;
    name: string;
    seoH1: string | null;
    children: CategoryItemType[];
    products: ListedProductEdgesType;
    readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
    linkedCategories: CategoryItemType[];
}
