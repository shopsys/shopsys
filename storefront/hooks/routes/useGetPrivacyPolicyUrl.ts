import { usePrivacyPolicyArticleUrlQueryApi } from 'graphql/generated';

export const useGetPrivacyPolicyUrl = (): string => {
    const [{ data }] = usePrivacyPolicyArticleUrlQueryApi();

    return data?.privacyPolicyArticle?.slug ?? '#';
};
