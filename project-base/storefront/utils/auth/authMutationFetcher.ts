import { AUTH_CLEAR_ENDPOINT, AUTH_DOMAIN_ID_HEADER, AUTH_MUTATION_ENDPOINT } from 'utils/auth/authConstants';
import { DomainConfigType } from 'utils/domain/domainConfig';

export const getAuthMutationFetcher = (domainConfig: DomainConfigType): typeof fetch => {
    return (_input, init) =>
        fetch(AUTH_MUTATION_ENDPOINT, {
            body: init?.body,
            cache: 'no-store',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                [AUTH_DOMAIN_ID_HEADER]: domainConfig.domainId.toString(),
            },
            method: 'POST',
            signal: init?.signal,
        });
};

export const clearAuthCookies = async (domainConfig: DomainConfigType): Promise<void> => {
    await fetch(AUTH_CLEAR_ENDPOINT, {
        cache: 'no-store',
        credentials: 'same-origin',
        headers: {
            [AUTH_DOMAIN_ID_HEADER]: domainConfig.domainId.toString(),
        },
        method: 'POST',
    });
};
