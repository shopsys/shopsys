import { DomainConfigType } from 'utils/domain/domainConfig';
import { getLocalePrefix } from 'utils/domain/domainUtils';
import { isClient } from 'utils/isClient';
import { getInternationalizedStaticUrls } from 'utils/staticUrls/getInternationalizedStaticUrls';

const GO_PAY_PAYMENT_SESSION_LOCAL_STORAGE_KEY = 'goPayPaymentSession' as const;
const MAX_GO_PAY_PAYMENT_SESSION_AGE_MS = 30 * 60 * 1000;

type GoPayPaymentSessionData = {
    orderUuid: string;
    orderUrlHash?: string;
    orderPaymentStatusPageValidityHash: string;
    domainUrl: string;
    forceRedirectAfterInlineReturn: boolean;
    timestamp: number;
};

type GoPayPaymentSessionInput = Omit<GoPayPaymentSessionData, 'timestamp' | 'forceRedirectAfterInlineReturn'> & {
    forceRedirectAfterInlineReturn?: boolean;
};

const normalizeDomainUrl = (domainUrl?: string) => domainUrl?.replace(/\/+$/, '');

export const saveGoPayPaymentSession = (session: GoPayPaymentSessionInput): void => {
    if (!isClient) {
        return;
    }

    const data: GoPayPaymentSessionData = {
        ...session,
        domainUrl: normalizeDomainUrl(session.domainUrl) ?? session.domainUrl,
        forceRedirectAfterInlineReturn: session.forceRedirectAfterInlineReturn ?? false,
        timestamp: Date.now(),
    };

    try {
        localStorage.setItem(GO_PAY_PAYMENT_SESSION_LOCAL_STORAGE_KEY, JSON.stringify(data));
    } catch {
        // Ignore storage errors, the payment flow can continue without local backup recovery.
    }
};

export const getGoPayPaymentSession = (currentDomainUrl?: string): GoPayPaymentSessionData | null => {
    if (!isClient) {
        return null;
    }

    const stringifiedData = localStorage.getItem(GO_PAY_PAYMENT_SESSION_LOCAL_STORAGE_KEY);

    if (stringifiedData === null) {
        return null;
    }

    try {
        const parsedData = JSON.parse(stringifiedData) as Partial<GoPayPaymentSessionData>;
        const data: GoPayPaymentSessionData = {
            orderUuid: parsedData.orderUuid ?? '',
            orderUrlHash: parsedData.orderUrlHash,
            orderPaymentStatusPageValidityHash: parsedData.orderPaymentStatusPageValidityHash ?? '',
            domainUrl: parsedData.domainUrl ?? '',
            forceRedirectAfterInlineReturn: parsedData.forceRedirectAfterInlineReturn ?? false,
            timestamp: parsedData.timestamp ?? 0,
        };

        if (currentDomainUrl && normalizeDomainUrl(data.domainUrl) !== normalizeDomainUrl(currentDomainUrl)) {
            return null;
        }

        const age = Date.now() - data.timestamp;
        if (age > MAX_GO_PAY_PAYMENT_SESSION_AGE_MS) {
            localStorage.removeItem(GO_PAY_PAYMENT_SESSION_LOCAL_STORAGE_KEY);

            return null;
        }

        return data;
    } catch {
        localStorage.removeItem(GO_PAY_PAYMENT_SESSION_LOCAL_STORAGE_KEY);

        return null;
    }
};

export const removeGoPayPaymentSession = (): void => {
    if (!isClient) {
        return;
    }

    localStorage.removeItem(GO_PAY_PAYMENT_SESSION_LOCAL_STORAGE_KEY);
};

export const getGoPayPaymentSessionForOrder = (
    currentDomainUrl: string,
    orderUuid: string,
): GoPayPaymentSessionData | null => {
    const session = getGoPayPaymentSession(currentDomainUrl);

    if (!session || session.orderUuid !== orderUuid) {
        return null;
    }

    return session;
};

export const shouldOpenGoPayAsRedirectOnly = (currentDomainUrl: string, orderUuid: string): boolean =>
    !!getGoPayPaymentSessionForOrder(currentDomainUrl, orderUuid)?.forceRedirectAfterInlineReturn;

export const markGoPayPaymentSessionForRedirectOnly = (currentDomainUrl: string, orderUuid: string): boolean => {
    const session = getGoPayPaymentSessionForOrder(currentDomainUrl, orderUuid);

    if (!session || session.forceRedirectAfterInlineReturn) {
        return !!session;
    }

    saveGoPayPaymentSession({
        ...session,
        forceRedirectAfterInlineReturn: true,
    });

    return true;
};

export const buildPaymentConfirmationUrlFromSession = (
    domainConfig: DomainConfigType,
    orderUuid: string,
): string | null => {
    const session = getGoPayPaymentSessionForOrder(domainConfig.url, orderUuid);

    if (!session) {
        return null;
    }

    const [orderPaymentConfirmationUrl] = getInternationalizedStaticUrls(
        ['/order-payment-confirmation'],
        domainConfig.url,
    );
    const localePrefix = getLocalePrefix(domainConfig);
    const params = new URLSearchParams({
        orderIdentifier: session.orderUuid,
        orderPaymentStatusPageValidityHash: session.orderPaymentStatusPageValidityHash,
    });
    if (session.orderUrlHash) {
        params.set('orderUrlHash', session.orderUrlHash);
    }

    return `${localePrefix}${orderPaymentConfirmationUrl}?${params.toString()}`;
};
