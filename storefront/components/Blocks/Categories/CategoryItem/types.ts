import { ImageType } from 'components/Basic/Image/types';
import { v4 as uuid } from 'uuid';

export type CategoryItemApiType = {
    name: string;
    uuid: typeof uuid;
    slug: string;
    images: ImageType[];
    products?: {
        totalCount: number;
    };
};

export type CategoryItemType = {
    name: string;
    uuid: typeof uuid;
    slug: string;
    image: ImageType | null;
    products?: {
        totalCount: number;
    };
};
