import { BreadcrumbType } from 'connectors/breadcrumb/Breadcrumb';
import { SlugType } from 'connectors/slug/Slug';
import { v4 as uuid } from 'uuid';

export const blogCategoryBody = `
    uuid
    blogCategoryName: name
    blogArticles {
        edges {
            node {
                name
                createdAt
                perex
                link
                blogCategories {
                    name
                    link
                    parent {
                        name
                    }
                }
            }
        }
    }
    breadcrumb {
        name
        slug
    }` as const;

export type BlogArticlesType = {
    edges: {
        node: {
            name: string;
            createdAt: string;
            perex: string;
            link: string;
            blogCategories: {
                name: string;
                link: string;
                parent: {
                    name: string;
                };
            }[];
        };
    }[];
};

type BlogArticleItemType = {
    node: {
        name: string;
        createdAt: string;
        perex: string;
        link: string;
        blogCategories: {
            name: string;
            link: string;
            parent: {
                name: string;
            };
        }[];
    };
};

export interface BlogCategoryType extends SlugType, BreadcrumbType {
    uuid: typeof uuid;
    blogCategoryName: string;
    blogArticles: BlogArticlesType;
}

function mapBlogCategoryArticles(blogArticles: BlogArticleItemType[]) {
    const mappedBlogCategoryArticles = [];
    for (const blogArticle of blogArticles) {
        mappedBlogCategoryArticles.push({
            node: {
                ...blogArticle.node,
                createdAt: blogArticle.node.createdAt.replace(/T.*$/g, ''),
            },
        });
    }

    return mappedBlogCategoryArticles;
}

export function mapBlogCategoryData(apiBlogCategoryData: BlogCategoryType): BlogCategoryType {
    return {
        ...apiBlogCategoryData,
        blogArticles: {
            edges: mapBlogCategoryArticles(apiBlogCategoryData.blogArticles.edges),
        },
    };
}
