import { ImageType } from '../../../basic/ShopsysImage/types';

export type CategoryItemType = {
    image: ImageType | null;
    name: string;
    slug: string;
    uuid: string;
};

export type CategoryItemApiType = {
    images: ImageType[];
    name: string;
    slug: string;
    uuid: string;
};
