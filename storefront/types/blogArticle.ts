import { PageInfoType, SliderProductItemType } from 'types/product';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { ImageSizeType } from 'types/image';
import { SimpleBlogCategoryType } from './blogCategory';

export type BlogArticleDetailType = {
    __typename: string | undefined;
    uuid: string;
    name: string;
    slug: string;
    link: string;
    image: ImageSizeType | null;
    breadcrumb: BreadcrumbItemType[];
    text: string | null;
    publishDate: string;
    blogArticleProducts: SliderProductItemType[];
};

export type BlogArticleConnectionType = {
    totalCount: number;
    pageInfo: PageInfoType;
    edges: ListedBlogArticleType[];
};

export type ListedBlogArticleType = {
    uuid: string;
    name: string;
    link: string;
    slug: string;
    image: ImageSizeType | null;
    publishDate: string;
    perex: string | null;
    blogCategories: SimpleBlogCategoryType[];
};

export type SimpleBlogArticleType = {
    __typename?: 'BlogArticle';
    name: string;
    slug: string;
    image: ImageSizeType | null;
};
