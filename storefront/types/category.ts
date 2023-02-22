import { ImageSizesFragmentApi } from 'graphql/generated';

export type ListedCategoryType = {
    uuid: string;
    name: string;
    slug: string;
    images: ImageSizesFragmentApi[];
    totalCount?: number;
};

export type ReadyCategorySeoMixLink = {
    name: string;
    slug: string;
};
