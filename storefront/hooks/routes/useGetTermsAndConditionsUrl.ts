import { useTermsAndConditionsArticleUrlQueryApi } from 'graphql/generated';

export const useGetTermsAndConditionsUrl = (): string => {
    const [{ data }] = useTermsAndConditionsArticleUrlQueryApi();

    return data?.termsAndConditionsArticle?.slug ?? '#';
};
