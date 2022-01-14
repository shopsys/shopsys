import { BlogArticlesType } from 'types/blogArticle';
import { BlogCategoryDetailFragmentApi } from 'graphql/generated';
import { BlogCategoryType } from 'types/blogCategory';
import { mapImageApiData } from 'connectors/image/Image';
import { mapPageInfoApiData } from 'connectors/pageInfo/PageInfo';

export function mapBlogCategoryData(apiBlogCategoryData: BlogCategoryDetailFragmentApi): BlogCategoryType {
    const blogArticles: BlogArticlesType = {
        ...apiBlogCategoryData.blogArticles,
        totalCount:
            apiBlogCategoryData.blogArticles?.totalCount !== undefined
                ? apiBlogCategoryData.blogArticles.totalCount
                : 0,
        pageInfo: mapPageInfoApiData(apiBlogCategoryData.blogArticles?.pageInfo),
        edges: [],
    };

    if (apiBlogCategoryData.blogArticles?.edges !== undefined && apiBlogCategoryData.blogArticles.edges !== null) {
        for (const edge of apiBlogCategoryData.blogArticles.edges) {
            if (edge?.node === undefined || edge.node === null) {
                continue;
            }
            blogArticles.edges.push({
                ...edge.node,
                perex: edge.node.perex !== null && edge.node.perex !== undefined ? edge.node.perex : undefined,
                image: mapImageApiData([edge.node.image]),
                blogCategories: edge.node.blogCategories.map((blogCategory) => ({
                    ...blogCategory,
                    parent:
                        blogCategory.parent !== null && blogCategory.parent !== undefined ? blogCategory.parent : null,
                })),
            });
        }
    }

    return {
        ...apiBlogCategoryData,
        __typename: 'BlogCategory',
        blogArticles: blogArticles,
    };
}
