import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';

export const useProductReviewPolicyArticleUrl = (): string | null => {
    const [{ data: settingsData }] = useSettingsQuery({ requestPolicy: 'cache-only' });

    return settingsData?.settings?.productReviewPolicyArticleUrl ?? null;
};
