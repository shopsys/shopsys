import { getCouldNotFindUserConsentPolicyArticleUrl } from './userConsentUtils';
import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const UserConsent = dynamic(
    () => import('components/Blocks/UserConsent/UserConsent').then((component) => ({
        default: component.UserConsent
    })),
    {
        ssr: false,
    },
);
export const DeferredUserConsent: FC<{ url: string }> = ({ url }) => {
    const [settingsQueryResponse] = useSettingsQuery();
    const shouldRender = useDeferredRender('user_consent');

    return shouldRender && !getCouldNotFindUserConsentPolicyArticleUrl(settingsQueryResponse) ? (
        <UserConsent url={url} />
    ) : null;
};
