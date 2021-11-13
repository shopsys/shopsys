import { ImageType } from 'components/Basic/Image/types';

export type SimpleArticleType = {
    __typename?: 'Article';
    name: string;
    slug: string;
};

export type SimpleBlogArticleType = {
    __typename?: 'BlogArticle';
    name: string;
    slug: string;
};

export type ListedArticleType = {
    name: string;
    slug: string;
    image: null;
};

export type ListedBlogArticleType = {
    name: string;
    slug: string;
    image: ImageType | null;
};
