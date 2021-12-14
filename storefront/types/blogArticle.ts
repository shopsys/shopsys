import { ImageType } from 'components/Basic/Image/types';

export type SimpleBlogArticleType = {
    __typename?: 'BlogArticle';
    name: string;
    slug: string;
};

export type ListedBlogArticleType = {
    name: string;
    slug: string;
    image: ImageType | null;
};
