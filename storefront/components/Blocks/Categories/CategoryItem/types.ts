import { ImageApiType, ImageType } from 'components/Basic/Image/types';

export type CategoryItemApiType = {
    name: string;
    uuid: string;
    slug: string;
    images: ImageApiType[];
    products?: {
        totalCount: number;
    };
};

export type CategoryItemType = {
    name: string;
    uuid: string;
    slug: string;
    image: ImageType | null;
    products?: {
        totalCount: number;
    };
};
