import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { getCookies } from 'cookies-next';
import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { useRouter } from 'next/router';
import { useEffect, useEffectEvent } from 'react';
import { usePersistStore } from 'store/usePersistStore';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { getCookieName } from 'utils/cookies/cookieNaming';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';
import { getIsHttps } from 'utils/requestProtocol';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const useAuthLoader = () => {
    const { t } = useTranslation();
    const authLoading = usePersistStore((store) => store.authLoading);
    const domainConfig = useDomainConfig();

    const router = useRouter();
    const isUserLoggedIn = useIsUserLoggedIn();
    const [{ fetching: isCustomerUserFetching }] = useCurrentCustomerUserQuery();
    const slug = getUrlWithoutGetParameters(router.asPath);
    const updateAuthLoadingState = usePersistStore((store) => store.updateAuthLoadingState);

    const onTokenMismatchReload = useEffectEvent(() => {
        router.reload();
    });

    useEffect(() => {
        if (isCustomerUserFetching) {
            return;
        }

        const cookies = getCookies({ secure: getIsHttps() });
        const accessTokenName = getCookieName('accessToken', domainConfig.domainId);
        const refreshTokenName = getCookieName('refreshToken', domainConfig.domainId);
        const isWithUserTokens = !!(cookies[accessTokenName] && cookies[refreshTokenName]);

        if ((isUserLoggedIn && !isWithUserTokens) || (!isUserLoggedIn && isWithUserTokens)) {
            onTokenMismatchReload();
        }
    }, [slug, domainConfig.domainId, isUserLoggedIn, isCustomerUserFetching]);

    const onShowAuthToasts = useEffectEvent(() => {
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
    });

    useEffect(() => {
        onShowAuthToasts();
    }, []);
};
