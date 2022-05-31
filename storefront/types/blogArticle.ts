import { SimpleBlogCategoryType } from './blogCategory';
import { BreadcrumbItemType } from 'types/breadcrumb';
import { ImageType } from 'types/image';
import { PageInfoType } from 'types/pageInfo';
import { SliderProductItemType } from 'types/product';

export type BlogArticleDetailType = {
    __typename?: 'BlogArticle';
    uuid: string;
    name: string;
    slug: string;
    link: string;
    image: ImageType | null;
    breadcrumb: BreadcrumbItemType[];
    text: string | null;
    publishDate: string;
    blogArticleProducts: SliderProductItemType[];
    seoTitle: string | null;
    seoMetaDescription: string | null;
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
    image: ImageType | null;
    publishDate: string;
    perex: string | null;
    blogCategories: SimpleBlogCategoryType[];
};

export type SimpleBlogArticleType = {
    __typename?: 'BlogArticle';
    name: string;
    slug: string;
    image: ImageType | null;
};
