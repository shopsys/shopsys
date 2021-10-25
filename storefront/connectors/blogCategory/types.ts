import { ImageApiType, ImageType } from 'components/Basic/Image/types';
import { BreadcrumbType } from 'connectors/breadcrumb/Breadcrumb';
import { SlugType } from 'connectors/slug/Slug';

export type BlogArticleItemApiData = {
    node: {
        uuid: string;
        name: string;
        createdAt: string;
        perex: string;
        link: string;
        image: ImageApiType;
        blogCategories: {
            uuid: string;
            name: string;
            link: string;
            parent: {
                name: string;
            };
        }[];
    };
};

export type BlogArticlesApiData = {
    totalCount: number;
    pageInfo: {
        startCursor: string;
        endCursor: string;
        hasNextPage: string;
        hasPreviousPage: string;
    };
    edges: BlogArticleItemApiData[];
};

export interface BlogCategoryApiData extends SlugType, BreadcrumbType {
    uuid: string;
    blogCategoryName: string;
    blogArticles: BlogArticlesApiData;
}

export type BlogArticleType = {
    uuid: string;
    name: string;
    createdAt: string;
    perex: string;
    link: string;
    image: ImageType | null;
    blogCategories: {
        uuid: string;
        name: string;
        link: string;
        parent: {
            name: string;
        };
    }[];
};

export type BlogArticlesType = {
    totalCount: number;
    pageInfo: {
        startCursor: string;
        endCursor: string;
        hasNextPage: string;
        hasPreviousPage: string;
    };
    edges: BlogArticleType[];
};

export interface BlogCategoryType extends SlugType, BreadcrumbType {
    uuid: string;
    blogCategoryName: string;
    blogArticles: BlogArticlesType;
}
