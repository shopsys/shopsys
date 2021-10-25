import { BlogArticleItemApiData, BlogArticleType, BlogCategoryApiData, BlogCategoryType } from './types';

export function blogCategoryBody(blogPaginationEndCursor: string): string {
    return `
    uuid
    blogCategoryName: name
    blogArticles(after:"${blogPaginationEndCursor}") {
        totalCount
        pageInfo {
            startCursor
            endCursor
            hasNextPage
            hasPreviousPage
        }
        edges {
            node {
                uuid
                name
                createdAt
                perex
                link
                image(sizes: "list") {
                    sizes {
                        size
                        url
                        width
                        height
                    }
                }
                blogCategories {
                    uuid
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
    }
    `;
}

function mapBlogCategoryArticles(blogArticles: BlogArticleItemApiData[]): BlogArticleType[] {
    const mappedBlogCategoryArticles = [];
    for (const blogArticle of blogArticles) {
        mappedBlogCategoryArticles.push({
            ...blogArticle.node,
            image: blogArticle.node.image !== null ? blogArticle.node.image.sizes[0] : null,
        });
    }

    return mappedBlogCategoryArticles;
}

export function mapBlogCategoryData(apiBlogCategoryData: BlogCategoryApiData): BlogCategoryType {
    return {
        ...apiBlogCategoryData,
        blogArticles: {
            totalCount: apiBlogCategoryData.blogArticles.totalCount,
            pageInfo: {
                ...apiBlogCategoryData.blogArticles.pageInfo,
            },
            edges: mapBlogCategoryArticles(apiBlogCategoryData.blogArticles.edges),
        },
    };
}
