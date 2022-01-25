import { BreadcrumbItemType } from 'types/breadcrumb';
import { ImageSizeType } from 'types/image';
import { ListedProductConnectionType } from 'types/product';

export type BrandDetailType = {
    __typename: 'Brand';
    slug: string;
    uuid: string;
    breadcrumb: BreadcrumbItemType[];
    name: string;
    seoH1: string | null;
    image: ImageSizeType | null;
    description: string | null;
    productConnection: ListedProductConnectionType;
};

export type ListedBrandType = {
    uuid: string;
    name: string;
    slug: string;
    image: ImageSizeType | null;
};

export type SimpleBrandType = {
    __typename?: 'Brand';
    name: string;
    slug: string;
};
