import { Error404Content } from 'components/Pages/ErrorPage/404/Error404Content';
import { useStaticUrlGuard } from 'hooks/staticUrls/UseStaticUrlGuard';
import { useRouter } from 'next/router';
import { FC } from 'react';

type StaticUrlGuardProps = {
    domainUrl: string;
};

export const StaticUrlGuard: FC<StaticUrlGuardProps> = (props) => {
    const router = useRouter();
    const isStaticUrlAllowed = useStaticUrlGuard(router.asPath.split('?')[0], props.domainUrl);

    if (!isStaticUrlAllowed) {
        return <Error404Content />;
    }

    return <>{props.children}</>;
};
