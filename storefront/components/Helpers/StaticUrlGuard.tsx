import DefaultErrorPage from 'next/error';
import { FC } from 'react';
import { useRouter } from 'next/router';
import { useStaticUrlGuard } from 'hooks/staticUrls/UseStaticUrlGuard';

type StaticUrlGuardProps = {
    domainUrl: string;
};

const StaticUrlGuard: FC<StaticUrlGuardProps> = (props) => {
    const router = useRouter();
    const isStaticUrlAllowed = useStaticUrlGuard(router.asPath, props.domainUrl);

    if (!isStaticUrlAllowed) {
        return <DefaultErrorPage statusCode={404} />;
    }

    return <>{props.children}</>;
};

export default StaticUrlGuard;
