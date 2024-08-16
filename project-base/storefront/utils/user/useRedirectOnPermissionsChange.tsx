import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import useTranslation from 'next-translate/useTranslation';
import { useRouter } from 'next/router';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';

const REDIRECT_TIMEOUT = 2000;

export const useRedirectOnPermissionsChange = () => {
    const { t } = useTranslation();
    const { url } = useDomainConfig();
    const { push } = useRouter();
    const [customerUrl] = getInternationalizedStaticUrls(['/customer'], url);

    const redirect = (message?: string) => {
        showErrorMessage(message ?? t('Your permissions have changed. You are being redirected'));
        setTimeout(() => {
            push(customerUrl);
        }, REDIRECT_TIMEOUT);
    };

    return {
        redirect,
    };
};
