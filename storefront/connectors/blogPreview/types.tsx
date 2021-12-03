import { BlogCategoryFragmentApi } from 'graphql/generated';
import { ImageType } from 'components/Basic/Image/types';

export type BlogPreviewType = {
    name: string;
    link: string;
    perex: string;
    image: ImageType | null;
    blogCategories: BlogCategoryFragmentApi[];
};
