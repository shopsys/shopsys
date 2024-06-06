import { getCouldNotFindUserConsentPolicyArticleUrl } from './userConsentUtils';
import { useUserConsentPolicyArticleUrlQuery } from 'graphql/requests/articles/queries/UserConsentPolicyArticleUrlQuery.generated';
import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const UserConsent = dynamic(
    () => import('components/Blocks/UserConsent/UserConsent').then((component) => component.UserConsent),
    {
        ssr: false,
    },
);
export const DeferredUserConsent: FC<{ url: string }> = ({ url }) => {
    const [{ error: userConsentPolicyArticleUrlError }] = useUserConsentPolicyArticleUrlQuery();
    const shouldRender = useDeferredRender('user_consent');

    return shouldRender && !getCouldNotFindUserConsentPolicyArticleUrl(userConsentPolicyArticleUrlError) ? (
        <UserConsent url={url} />
    ) : null;
};
