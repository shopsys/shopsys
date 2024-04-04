import { useRouter } from 'next/router';
import { isClient } from 'utils/isClient';

type PageGuardProps = {
    isWithAccess: boolean;
    errorRedirectUrl: string;
};

export const PageGuard: FC<PageGuardProps> = ({ isWithAccess, errorRedirectUrl, children }) => {
    const router = useRouter();

    if (!isWithAccess) {
        if (isClient) {
            router.replace(errorRedirectUrl);
        }

        return null;
    }

    return <>{children}</>;
};
