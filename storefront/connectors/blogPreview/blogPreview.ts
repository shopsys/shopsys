import { BlogListQueryApi, useBlogListQueryApi } from 'graphql/generated';
import { BlogPreviewType } from 'connectors/blogPreview/types';
import { mapImageApiData } from 'connectors/image/Image';
import { useQueryError } from 'hooks/graphQl/UseQueryError';

export const blogPreviewVariables = { first: 6, onlyHomepageArticles: true };

const mapBlogPreview = (blogArticles: BlogListQueryApi['blogArticles'] | undefined): BlogPreviewType[] => {
    const blogArticleEdges = blogArticles?.edges;

    if (blogArticleEdges === null || blogArticleEdges === undefined) {
        return [];
    }

    const mappedBlogPreviewArticles = [];
    for (const blogArticle of blogArticleEdges) {
        if (blogArticle?.node === undefined || blogArticle?.node === null) {
            continue;
        }

        mappedBlogPreviewArticles.push({
            ...blogArticle.node,
            perex:
                blogArticle.node.perex !== undefined && blogArticle.node.perex !== null ? blogArticle.node.perex : '',
            image: mapImageApiData([blogArticle.node?.image]),
        });
    }

    return mappedBlogPreviewArticles;
};

export const getBlogPreviewArticles = (): BlogPreviewType[] => {
    const [{ data, error }] = useBlogListQueryApi({ variables: blogPreviewVariables });

    useQueryError(error);

    const blogPreviewApiData = data?.blogArticles;

    if (blogPreviewApiData === undefined || blogPreviewApiData === null) {
        return [];
    }

    return mapBlogPreview(blogPreviewApiData);
};
