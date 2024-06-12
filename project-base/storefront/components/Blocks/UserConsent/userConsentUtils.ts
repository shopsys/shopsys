import { TypeSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { OperationResult, UseQueryState } from 'urql';

export const getCouldNotFindUserConsentPolicyArticleUrl = (
    settingsQueryResponse: OperationResult<TypeSettingsQuery> | UseQueryState<TypeSettingsQuery>,
) =>
    settingsQueryResponse.data?.settings?.userConsentPolicyArticleUrl === null ||
    settingsQueryResponse.error?.graphQLErrors.some(
        (error) => error.extensions.userCode === 'article-not-found-user-consent-policy',
    );
