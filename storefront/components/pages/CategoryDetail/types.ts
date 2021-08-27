import { ListedProductEdgesType, ListedProductItemApiType } from '../../blocks/product/types';
import { BreadcrumbType } from '../../../connectors/breadcrumb/Breadcrumb';
import { SlugType } from '../../../connectors/slug/Slug';
import { v4 as uuid } from 'uuid';

interface SubcategoryWithProductsCount {
    name: string;
    uuid: typeof uuid;
    slug: string;
    products: {
        totalCount: number;
    };
}

export type ReadyCategorySeoMixLink = {
    name: string;
    slug: string;
};

export type CategoryDetailApiType = SlugType &
    BreadcrumbType & {
        uuid: typeof uuid;
        name: string;
        seoH1: string | null;
        children: SubcategoryWithProductsCount[];
        products: {
            edges: {
                node: ListedProductItemApiType;
            }[];
            totalCount: number;
        };
        readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
    };

export interface CategoryDetailType extends SlugType {
    uuid: typeof uuid;
    name: string;
    seoH1: string | null;
    children: SubcategoryWithProductsCount[];
    products: ListedProductEdgesType;
    readyCategorySeoMixLinks: ReadyCategorySeoMixLink[];
}
