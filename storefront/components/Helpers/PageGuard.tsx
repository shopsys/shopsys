import { FC } from 'react';
import { useRouter } from 'next/router';

type PageGuardProps = {
    accessCondition: boolean;
    errorRedirectUrl: string;
};

const PageGuard: FC<PageGuardProps> = (props) => {
    const router = useRouter();

    if (props.accessCondition) {
        return <>{props.children}</>;
    }

    if (typeof window !== 'undefined') {
        router.replace(props.errorRedirectUrl);
    }

    return null;
};

export default PageGuard;
