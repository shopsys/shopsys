import { SimpleCategoryFragmentApi } from 'graphql/generated';
import { ImageType } from 'types/image';

export type AdvertType = AdvertImageType | AdvertCodeType;

type AdvertCommonType = {
    uuid: string;
    type: string;
    positionName: string;
    name: string;
    categories: SimpleCategoryFragmentApi[];
};

type AdvertImageType = AdvertCommonType & {
    __typename: 'AdvertImage';
    image: ImageType | null;
    imageMobile: ImageType | null;
    link?: string;
};

type AdvertCodeType = AdvertCommonType & {
    __typename: 'AdvertCode';
    code: string;
};
