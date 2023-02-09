import { ImageSizesFragmentApi } from 'graphql/generated';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { ListedProductConnectionPreviewType } from 'types/product';

export type BrandDetailType = {
    __typename: 'Brand';
    slug: string;
    uuid: string;
    breadcrumb: BreadcrumbItemType[];
    name: string;
    seoH1: string | null;
    images: ImageSizesFragmentApi[];
    description: string | null;
    productConnection: ListedProductConnectionPreviewType;
    seoTitle: string | null;
    seoMetaDescription: string | null;
};

export type ListedBrandType = {
    uuid: string;
    name: string;
    slug: string;
    images: ImageSizesFragmentApi[];
};

export type SimpleBrandType = {
    __typename?: 'Brand';
    name: string;
    slug: string;
};
