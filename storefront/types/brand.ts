import { BreadcrumbItemType } from 'types/breadcrumb';
import { ImageType } from 'types/image';
import { ListedProductEdgesType } from 'components/Blocks/Product/types';

export type SimpleBrandType = {
    __typename?: 'Brand';
    name: string;
    slug: string;
};

export type ListedBrandType = {
    uuid: string;
    name: string;
    slug: string;
    image: ImageType | null;
};

export type BrandDetailType = {
    __typename: 'Brand';
    slug: string;
    uuid: string;
    breadcrumb: BreadcrumbItemType[];
    name: string;
    seoH1: string | null;
    image: ImageType | null;
    description: string | null;
    products: ListedProductEdgesType;
};
