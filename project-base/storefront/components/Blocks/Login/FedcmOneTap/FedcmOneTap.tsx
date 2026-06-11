import { useDomainConfig } from 'components/providers/DomainConfigProvider';
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
    params: Array<{ name: string; value: string }>;
};

type FedcmCredential = Credential & {
    token: string;
    configURL: string;
};

type FedcmIdentityRequestOptions = {
    identity: {
        providers: Array<{
            configURL: string;
            clientId: string;
            params?: Record<string, string>;
        }>;
    };
    mediation?: CredentialMediationRequirement;
};

// Cooldown after a user dismisses the FedCM prompt — keeps the experience non-intrusive across page navigations.
// Matches the rough cadence Google's gsi/client uses internally for One Tap exponential backoff.
const DISMISS_COOLDOWN_KEY_PREFIX = 'fedcm:dismissed:';
const DISMISS_COOLDOWN_MS = 7 * 24 * 60 * 60 * 1000;

const getDismissCooldownKey = (domainId: number): string => `${DISMISS_COOLDOWN_KEY_PREFIX}${domainId}`;

const isInDismissCooldown = (domainId: number): boolean => {
    try {
        const stored = window.localStorage.getItem(getDismissCooldownKey(domainId));

        if (stored === null) {
            return false;
        }

        const dismissedAt = Number.parseInt(stored, 10);

        return Number.isFinite(dismissedAt) && Date.now() < dismissedAt + DISMISS_COOLDOWN_MS;
    } catch {
        // localStorage may be disabled (private mode, quota, security policy) — fall through and let FedCM run.
        return false;
    }
};

const markFedcmDismissed = (domainId: number): void => {
    try {
        window.localStorage.setItem(getDismissCooldownKey(domainId), Date.now().toString());
    } catch {
        // localStorage may be disabled or full — accept the slightly worse UX rather than blocking sign-in.
    }
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

// The browser populates `configURL` on the returned `IdentityCredential` with the exact URL of the IdP that issued
// the token — the same string we passed in `providers[].configURL`. Matching it back against the configured
// providers gives us a deterministic provider type with no need to inspect the token payload.
const matchProviderTypeByConfigUrl = (
    configURL: string,
    fedcmProviders: FedcmProvider[],
): TypeLoginTypeEnum | null => fedcmProviders.find((provider) => provider.configUrl === configURL)?.type ?? null;

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
                providers: fedcmProviders.map((provider) => {
                    const extraParams = Object.fromEntries(provider.params.map(({ name, value }) => [name, value]));

                    return {
                        configURL: provider.configUrl,
                        clientId: provider.clientId,
                        params: { ...extraParams, nonce },
                    };
                }),
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

            const providerType = matchProviderTypeByConfigUrl(credential.configURL, fedcmProviders);

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
                // The user dismissed the FedCM prompt or the browser aborted the request — record the dismissal so
                // we don't pester them on every subsequent page navigation.
                markFedcmDismissed(domainId);

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
