import { CategoryItemApiType, CategoryItemType } from '../../Blocks/Categories/CategoryItem/types';
import { ListedProductEdgesType, ListedProductItemApiType } from '../../Blocks/Product/types';
import { BreadcrumbType } from 'connectors/breadcrumb/Breadcrumb';
import { SlugType } from '../../../connectors/slug/Slug';
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
