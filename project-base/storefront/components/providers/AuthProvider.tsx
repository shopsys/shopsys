'use client';

import { getCookies } from 'cookies-next';
import useTranslation from 'next-translate/useTranslation';
import { usePathname } from 'next/navigation';
import { useRouter } from 'next/navigation';
import { useEffect } from 'react';
import 'react-toastify/dist/ReactToastify.css';
import { usePersistStore } from 'store/usePersistStore';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';
import { getIsHttps } from 'utils/requestProtocol';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

interface AuthProviderProps {
    isUserLoggedIn: boolean;
}

export const AuthProvider: FC<AuthProviderProps> = ({ isUserLoggedIn, children }) => {
    const { t } = useTranslation();
    const authLoading = usePersistStore((store) => store.authLoading);
    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);

    const router = useRouter();
    const asPath = usePathname();
    const slug = getUrlWithoutGetParameters(asPath);

    useEffect(() => {
        const cookies = getCookies({ secure: getIsHttps() });
        const isWithUserTokens = !!(cookies.accessToken && cookies.refreshToken);

        if ((isUserLoggedIn && !isWithUserTokens) || (!isUserLoggedIn && isWithUserTokens)) {
            router.refresh();
        }
    }, [slug]);

    useEffect(() => {
        if (typeof authLoading === 'object' && authLoading?.authLoadingStatus === 'social-login-fail') {
            if (authLoading.socialNetworkType) {
                showErrorMessage(
                    t('Login via {{ socialNetworkType }} is not possible. Please register.', {
                        socialNetworkType: authLoading.socialNetworkType,
                    }),
                );
            } else {
                showErrorMessage(t('Login via the selected social network is not possible. Please register.'));
            }
        }

        if (authLoading === 'logout-loading') {
            showSuccessMessage(t('Successfully logged out'));
        }

        if (authLoading === 'registration-loading' || authLoading === 'registration-loading-with-cart-modifications') {
            showSuccessMessage(t('Your account has been created and you are logged in now'));
        }

        if (authLoading === 'login-loading' || authLoading === 'login-loading-with-cart-modifications') {
            showSuccessMessage(t('Successfully logged in'));
        }

        if (
            authLoading === 'registration-loading-with-cart-modifications' ||
            authLoading === 'login-loading-with-cart-modifications'
        ) {
            showInfoMessage(t('Your cart has been modified. Please check the changes.'));
        }

        updateAuthLoadingState(null);
    }, [authLoading]);

    return <>{children}</>;
};

export default AuthProvider;
