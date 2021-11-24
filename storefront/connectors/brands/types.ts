import { ImageType } from 'components/Basic/Image/types';

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
