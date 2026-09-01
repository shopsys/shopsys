import { useDomainConfig } from 'components/providers/DomainConfigProvider';
import { useCurrentCustomerUserQuery } from 'graphql/requests/customer/queries/CurrentCustomerUserQuery.generated';
import { useRouter } from 'next/router';
import { useEffect, useEffectEvent } from 'react';
import { getAuthNotification } from 'utils/auth/authNotificationStorage';
import { getAccessTokenFromCookies, hasRefreshTokenInCookies } from 'utils/auth/getTokensFromCookies';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { getUrlWithoutGetParameters } from 'utils/parsing/getUrlWithoutGetParameters';

export const useAuthStateSynchronization = () => {
    const domainConfig = useDomainConfig();
    const router = useRouter();
    const isUserLoggedIn = useIsUserLoggedIn();
    const [{ fetching: isCustomerUserFetching, stale: isCustomerUserStale }] = useCurrentCustomerUserQuery();
    const slug = getUrlWithoutGetParameters(router.asPath);

    const reloadOnAuthStateMismatch = useEffectEvent(() => {
        router.reload();
    });

    useEffect(() => {
        if (isCustomerUserFetching || isCustomerUserStale) {
            return;
        }

        const authNotification = getAuthNotification(domainConfig.domainId);
        if (typeof authNotification === 'string') {
            return;
        }

        const hasAuthCookies = !!getAccessTokenFromCookies(domainConfig) && hasRefreshTokenInCookies(domainConfig);

        if (isUserLoggedIn !== hasAuthCookies) {
            reloadOnAuthStateMismatch();
        }
    }, [slug, domainConfig.domainId, isUserLoggedIn, isCustomerUserFetching, isCustomerUserStale]);
};
