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

export interface CategoryDetailType extends SlugType {
    uuid: typeof uuid;
    name: string;
    seoH1: string | null;
    children: SubcategoryWithProductsCount[];
}
