import { SimpleBlogCategoryType } from './blogCategory';
import { ImageSizesFragmentApi } from 'graphql/generated';

export type ListedBlogArticleType = {
    uuid: string;
    name: string;
    link: string;
    slug: string;
    images: ImageSizesFragmentApi[];
    publishDate: string;
    perex: string | null;
    blogCategories: SimpleBlogCategoryType[];
};
