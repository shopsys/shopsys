import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { useRouter } from 'next/router';
import { useEffect, useEffectEvent, useState } from 'react';
import { AuthNotification } from 'types/auth';
import { consumeAuthNotification, getAuthNotification, hasAuthNotification } from 'utils/auth/authNotificationStorage';
import { getAccessTokenFromCookies, hasRefreshTokenInCookies } from 'utils/auth/getTokensFromCookies';
import useTranslation from 'utils/i18n/useTranslationWrapper';
import { showErrorMessage } from 'utils/toasts/showErrorMessage';
import { showInfoMessage } from 'utils/toasts/showInfoMessage';
import { showSuccessMessage } from 'utils/toasts/showSuccessMessage';

export const AuthNotificationLoader = () => {
    const { t } = useTranslation();
    const domainConfig = useDomainConfig();
    const router = useRouter();
    const [{ data: currentCustomerUserData, fetching: isCustomerUserFetching, stale: isCustomerUserStale }] =
        useCurrentCustomerUserQuery();
    const isUserLoggedIn = !!currentCustomerUserData?.currentCustomerUser;
    const hasAuthCookies = !!getAccessTokenFromCookies(domainConfig) && hasRefreshTokenInCookies(domainConfig);
    const isAuthStateSynchronized =
        !isCustomerUserFetching && !isCustomerUserStale && isUserLoggedIn === hasAuthCookies;
    // A notification created in this document must wait for the auth redirect to finish before it can be consumed.
    const [readyNotificationDomainId, setReadyNotificationDomainId] = useState<number | null>(() =>
        hasAuthNotification(domainConfig.domainId) ? domainConfig.domainId : null,
    );

    const showAuthNotification = useEffectEvent((authNotification: AuthNotification) => {
        if (typeof authNotification === 'object' && authNotification?.type === 'social-login-fail') {
            if (authNotification.socialNetworkType) {
                showErrorMessage(
                    t('Login via {{ socialNetworkType }} is not possible. Please register.', {
                        socialNetworkType: authNotification.socialNetworkType,
                    }),
                );
            } else {
                showErrorMessage(t('Login via the selected social network is not possible. Please register.'));
            }
        }

        if (authNotification === 'logout') {
            showSuccessMessage(t('Successfully logged out'));
        }

        if (authNotification === 'registration' || authNotification === 'registration-with-cart-modifications') {
            showSuccessMessage(t('Your account has been created and you are logged in now'));
        }

        if (authNotification === 'login' || authNotification === 'login-with-cart-modifications') {
            showSuccessMessage(t('Successfully logged in'));
        }

        if (
            authNotification === 'registration-with-cart-modifications' ||
            authNotification === 'login-with-cart-modifications'
        ) {
            showInfoMessage(t('Your cart has been modified. Please check the changes.'));
        }
    });

    const showStoredAuthNotification = useEffectEvent((): 'empty' | 'pending' | 'reloading' | 'shown' => {
        const authNotification = getAuthNotification(domainConfig.domainId);

        if (authNotification === null) {
            return 'empty';
        }

        if (
            typeof authNotification === 'string' &&
            !isCustomerUserFetching &&
            !isCustomerUserStale &&
            isUserLoggedIn !== hasAuthCookies
        ) {
            router.reload();

            return 'reloading';
        }

        if (!canShowAuthNotification(authNotification, isAuthStateSynchronized, isUserLoggedIn)) {
            return 'pending';
        }

        const consumedAuthNotification = consumeAuthNotification(domainConfig.domainId);
        if (consumedAuthNotification !== null) {
            showAuthNotification(consumedAuthNotification);
        }

        return 'shown';
    });

    const markStoredAuthNotificationAsReady = useEffectEvent(() => {
        setReadyNotificationDomainId(domainConfig.domainId);
    });

    useEffect(() => {
        router.events.on('routeChangeComplete', markStoredAuthNotificationAsReady);

        return () => router.events.off('routeChangeComplete', markStoredAuthNotificationAsReady);
    }, [router.events]);

    useEffect(() => {
        if (readyNotificationDomainId !== domainConfig.domainId) {
            return;
        }

        if (showStoredAuthNotification() !== 'pending') {
            setReadyNotificationDomainId(null);
        }
    }, [
        domainConfig.domainId,
        isAuthStateSynchronized,
        isCustomerUserFetching,
        isCustomerUserStale,
        isUserLoggedIn,
        readyNotificationDomainId,
    ]);

    return null;
};

const canShowAuthNotification = (
    authNotification: AuthNotification,
    isAuthStateSynchronized: boolean,
    isUserLoggedIn: boolean,
): boolean => {
    if (typeof authNotification === 'object') {
        return true;
    }

    if (!isAuthStateSynchronized) {
        return false;
    }

    return authNotification === 'logout' ? !isUserLoggedIn : isUserLoggedIn;
};
