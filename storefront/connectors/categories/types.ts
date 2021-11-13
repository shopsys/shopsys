import { ImageType } from 'components/Basic/Image/types';

export type SimpleCategoryType = {
    __typename?: 'Category';
    name: string;
    slug: string;
};

export type ListedCategoryType = {
    uuid: string;
    name: string;
    slug: string;
    image: ImageType | null;
    totalCount?: number;
};
