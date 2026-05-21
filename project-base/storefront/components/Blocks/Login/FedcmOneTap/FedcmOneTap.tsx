import { useSettingsQuery } from 'graphql/requests/settings/queries/SettingsQuery.generated';
import { TypeLoginTypeEnum } from 'graphql/types';
import { useCallback, useEffect, useMemo, useRef } from 'react';
import { getTokensFromCookies } from 'utils/auth/getTokensFromCookies';
import { useIsUserLoggedIn } from 'utils/auth/useIsUserLoggedIn';
import { useLoginWithCredential } from 'utils/auth/useLoginWithCredential';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { logException } from 'utils/errors/logException';

type FedcmProvider = {
    type: TypeLoginTypeEnum;
    clientId: string;
    configUrl: string;
    autoSelect: boolean;
};

type FedcmCredential = Credential & {
    token: string;
};

type FedcmIdentityRequestOptions = {
    identity: {
        providers: Array<{
            configURL: string;
            clientId: string;
            params?: {
                nonce?: string;
            };
        }>;
    };
    mediation?: CredentialMediationRequirement;
};

const generateNonce = (): string => {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    // Fallback for environments without crypto.randomUUID — use a 16-byte random value as hex string.
    const buffer = new Uint8Array(16);
    crypto.getRandomValues(buffer);

    return Array.from(buffer, (byte) => byte.toString(16).padStart(2, '0')).join('');
};

const BASE64_URL_PATTERN = /^[A-Za-z0-9_-]+$/;

const isLikelyJwt = (token: string): boolean => {
    const parts = token.split('.');

    return parts.length === 3 && parts.every((part) => part.length > 0 && BASE64_URL_PATTERN.test(part));
};

// FedCM's `IdentityCredential` does not (yet) expose which provider issued the token, so we have to infer it from
// the token shape: Google delivers a JWT id_token (header.payload.signature), Seznam delivers a plain OAuth
// authorization code. This is good enough while Google is the only JWT-issuing FedCM provider we support; revisit
// if Apple or another JWT-based IdP is added.
const inferProviderTypeFromToken = (token: string, fedcmProviders: FedcmProvider[]): TypeLoginTypeEnum | null => {
    if (isLikelyJwt(token)) {
        return fedcmProviders.find((provider) => provider.type === TypeLoginTypeEnum.Google)?.type ?? null;
    }

    return fedcmProviders.find((provider) => provider.type !== TypeLoginTypeEnum.Google)?.type ?? null;
};

const isDismissError = (error: unknown): boolean => {
    if (!(error instanceof DOMException)) {
        return false;
    }

    return error.name === 'NotAllowedError' || error.name === 'AbortError';
};

// After a successful FedCM login the storefront stores tokens in cookies *before* triggering router.reload(),
// so on the next page load the cookies are populated long before urql finishes refetching the CurrentCustomerUser
// query. Using the cookies as a synchronous "already authenticated" signal avoids racing against the GraphQL
// loading state and re-showing the prompt to a logged-in user.
const hasAuthCookiesForDomain = (domainConfig: DomainConfigType): boolean => {
    const { accessToken, refreshToken } = getTokensFromCookies(domainConfig);

    return accessToken !== undefined || refreshToken !== undefined;
};

export const FedcmOneTap: FC = () => {
    const [{ data: settingsData }] = useSettingsQuery();
    const isUserLoggedIn = useIsUserLoggedIn();
    const loginWithCredential = useLoginWithCredential();
    const domainConfig = useDomainConfig();
    const { domainId } = domainConfig;
    const hasAttemptedRef = useRef(false);

    const fedcmProviders = useMemo<FedcmProvider[]>(
        () => (settingsData?.settings?.fedcmProviders ?? []) as FedcmProvider[],
        [settingsData?.settings?.fedcmProviders],
    );

    const triggerFedcm = useCallback(async () => {
        if (hasAttemptedRef.current || fedcmProviders.length === 0) {
            return;
        }

        if (!('IdentityCredential' in window)) {
            return;
        }

        if (hasAuthCookiesForDomain(domainConfig)) {
            return;
        }

        if (isInDismissCooldown(domainId)) {
            return;
        }

        hasAttemptedRef.current = true;

        const allProvidersAutoSelect = fedcmProviders.every((provider) => provider.autoSelect);
        const nonce = generateNonce();
        const options: FedcmIdentityRequestOptions = {
            identity: {
                providers: fedcmProviders.map((provider) => ({
                    configURL: provider.configUrl,
                    clientId: provider.clientId,
                    params: { nonce },
                })),
            },
            mediation: allProvidersAutoSelect ? 'silent' : 'optional',
        };

        try {
            const credential = (await navigator.credentials.get(
                options as unknown as CredentialRequestOptions,
            )) as FedcmCredential | null;

            if (!credential?.token) {
                return;
            }

            const providerType = inferProviderTypeFromToken(credential.token, fedcmProviders);

            if (providerType === null) {
                return;
            }

            await loginWithCredential({
                type: providerType,
                credential: credential.token,
                nonce,
            });
        } catch (error) {
            if (isDismissError(error)) {
                // The user dismissed the FedCM prompt or the browser aborted the request — this is part of the
                // normal flow, not a fault we should surface or log.
                return;
            }

            logException({ reason: error, location: 'FedcmOneTap.tsx:triggerFedcm' });
        }
    }, [fedcmProviders, loginWithCredential, domainConfig, domainId]);

    useEffect(() => {
        if (isUserLoggedIn) {
            return;
        }

        triggerFedcm();
    }, [isUserLoggedIn, triggerFedcm]);

    return null;
};
