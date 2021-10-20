import { CategoryItemApiType, CategoryItemType } from 'components/Blocks/Categories/CategoryItem/types';
import { ListedProductEdgesType, ListedProductItemApiType, PageInfoType } from 'components/Blocks/Product/types';
import { BreadcrumbType } from 'connectors/breadcrumb/Breadcrumb';
import { SlugType } from 'connectors/slug/Slug';
import { v4 as uuid } from 'uuid';

export type ReadyCategorySeoMixLink = {
    name: string;
    slug: string;
};

export type CategoryDetailApiType = SlugType &
    BreadcrumbType & {
        uuid: typeof uuid;
        name: string;
        seoH1: string | null;
        children: CategoryItemApiType[];
        linkedCategories: CategoryItemApiType[];
        products: {
            edges: {
                node: ListedProductItemApiType;
            }[];
            totalCount: number;
            pageInfo: PageInfoType;
        };
        readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
    };

export interface CategoryDetailType extends SlugType, BreadcrumbType {
    uuid: typeof uuid;
    name: string;
    seoH1: string | null;
    children: CategoryItemType[];
    products: ListedProductEdgesType;
    readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
    linkedCategories: CategoryItemType[];
}
