import { useRouter } from 'next/router';
import { FC } from 'react';

type PageGuardProps = {
    accessCondition: boolean;
    errorRedirectUrl: string;
};

export const PageGuard: FC<PageGuardProps> = ({ accessCondition, errorRedirectUrl, children }) => {
    const router = useRouter();

    if (!accessCondition) {
        if (typeof window !== 'undefined') {
            router.replace(errorRedirectUrl);
        }

        return null;
    }

    return <>{children}</>;
};
