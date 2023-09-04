import { useRouter } from 'next/router';

type PageGuardProps = {
    isWithAccess: boolean;
    errorRedirectUrl: string;
};

export const PageGuard: FC<PageGuardProps> = ({ isWithAccess, errorRedirectUrl, children }) => {
    const router = useRouter();

    if (!isWithAccess) {
        router.replace(errorRedirectUrl);
        return null;
    }

    return <>{children}</>;
};
