import { CombinedError } from 'urql';

export const getCouldNotFindUserConsentPolicyArticleUrl = (
    userConsentPolicyArticleUrlError: CombinedError | undefined,
) =>
    userConsentPolicyArticleUrlError?.graphQLErrors.some(
        (error) => error.extensions.userCode === 'article-not-found-user-consent-policy',
    );
