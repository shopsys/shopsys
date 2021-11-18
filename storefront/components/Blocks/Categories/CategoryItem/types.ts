import { ImageType } from 'components/Basic/Image/types';

export type CategoryItemType = {
    name: string;
    uuid: string;
    slug: string;
    image: ImageType | null;
    products?: {
        totalCount: number;
    };
};
