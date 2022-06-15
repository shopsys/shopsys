import { useCookiesArticleUrlQueryApi } from 'graphql/generated';

export const useGetCookiesUrl = (): string => {
    const [{ data }] = useCookiesArticleUrlQueryApi();

    return data?.cookiesArticle?.slug ?? '#';
};
