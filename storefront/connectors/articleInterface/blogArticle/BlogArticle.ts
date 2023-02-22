import { BlogArticleConnectionFragmentApi, useBlogArticlesQueryApi } from 'graphql/generated';
import { useQueryError } from 'hooks/graphQl/useQueryError';

export const blogPreviewVariables = { first: 6, onlyHomepageArticles: true };

export const useBlogPreviewArticles = (): BlogArticleConnectionFragmentApi | undefined => {
    const [{ data, error }] = useBlogArticlesQueryApi({ variables: blogPreviewVariables });

    useQueryError(error);

    return data?.blogArticles;
};
