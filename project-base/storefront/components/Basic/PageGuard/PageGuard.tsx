import { useRouter } from 'next/router';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { isClient } from 'utils/isClient';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

type PageGuardProps = {
    isWithAccess: boolean;
    errorRedirectUrl: string;
};

export const PageGuard: FC<PageGuardProps> = ({ isWithAccess, errorRedirectUrl, children }) => {
    const router = useRouter();
    const { t } = useTranslation();

    if (!isWithAccess) {
        if (isClient) {
            showErrorMessage(t('Requested page cannot be accessed.'));
            router.replace(errorRedirectUrl);
        }

        return null;
    }

    return <>{children}</>;
};
