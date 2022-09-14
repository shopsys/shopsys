import { Error404Content } from 'components/Pages/ErrorPage/404/Error404Content';
import { useStaticUrlGuard } from 'hooks/staticUrls/useStaticUrlGuard';
import { useRouter } from 'next/router';
import { FC } from 'react';

type StaticUrlGuardProps = {
    domainUrl: string;
};

export const StaticUrlGuard: FC<StaticUrlGuardProps> = ({ domainUrl, children }) => {
    const router = useRouter();
    const isStaticUrlAllowed = useStaticUrlGuard(router.asPath.split('?')[0], domainUrl);

    if (!isStaticUrlAllowed) {
        return <Error404Content />;
    }

    return <>{children}</>;
};
