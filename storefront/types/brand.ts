import { ImageSizesFragmentApi } from 'graphql/generated';

export type ListedBrandType = {
    uuid: string;
    name: string;
    slug: string;
    images: ImageSizesFragmentApi[];
};
