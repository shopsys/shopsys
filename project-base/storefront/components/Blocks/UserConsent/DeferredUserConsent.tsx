import dynamic from 'next/dynamic';
import { useDeferredRender } from 'utils/useDeferredRender';

const UserConsent = dynamic(
    () => import('components/Blocks/UserConsent/UserConsent').then((component) => component.UserConsent),
    {
        ssr: false,
    },
);
export const DeferredUserConsent: FC<{ url: string }> = ({ url }) => {
    const shouldRender = useDeferredRender('user_consent');

    return shouldRender ? <UserConsent url={url} /> : null;
};
