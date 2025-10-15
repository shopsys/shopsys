import { TypeSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.ssr';
import { CombinedError } from 'urql';

export const getCouldNotFindUserConsentPolicyArticleUrl = (
    settingsData: TypeSettingsQuery | null | undefined,
    settingsError: CombinedError | undefined,
) =>
    settingsData?.settings?.userConsentPolicyArticleUrl === null ||
    settingsError?.graphQLErrors.some((error) => error.extensions.userCode === 'article-not-found-user-consent-policy');
