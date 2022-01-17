import Error404 from 'components/Pages/ErrorPage/404';
import { FC } from 'react';
import { useRouter } from 'next/router';
import { useStaticUrlGuard } from 'hooks/staticUrls/UseStaticUrlGuard';

type StaticUrlGuardProps = {
    domainUrl: string;
};

const StaticUrlGuard: FC<StaticUrlGuardProps> = (props) => {
    const router = useRouter();
    const isStaticUrlAllowed = useStaticUrlGuard(router.asPath.split('?')[0], props.domainUrl);

    if (!isStaticUrlAllowed) {
        return <Error404 />;
    }

    return <>{props.children}</>;
};

export default StaticUrlGuard;
