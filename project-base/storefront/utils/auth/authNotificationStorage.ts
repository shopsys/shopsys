import { AuthNotification } from 'types/auth';
import { getAllowedSocialNetworkType } from 'utils/auth/getAllowedSocialNetworkType';
import { logException } from 'utils/errors/logException';
import { isClient } from 'utils/isClient';

const AUTH_NOTIFICATION_STORAGE_KEY = 'shopsys-platform-auth-notification';

// the current document is about to navigate away, so its own notifications belong to the next document
const domainIdsWithNotificationStoredInCurrentDocument = new Set<number>();

const stringAuthNotifications: AuthNotification[] = [
    'login',
    'login-with-cart-modifications',
    'logout',
    'registration',
    'registration-with-cart-modifications',
];

export const storeAuthNotification = (domainId: number, authNotification: AuthNotification): void => {
    if (!isClient) {
        return;
    }

    try {
        sessionStorage.setItem(getStorageKey(domainId), JSON.stringify(authNotification));
        domainIdsWithNotificationStoredInCurrentDocument.add(domainId);
    } catch (error) {
        logException(error);
    }
};

export const getAuthNotification = (domainId: number): AuthNotification | null => {
    if (!isClient) {
        return null;
    }

    const storageKey = getStorageKey(domainId);

    try {
        const storedAuthNotification = sessionStorage.getItem(storageKey);

        if (storedAuthNotification === null) {
            return null;
        }

        const authNotification = normalizeAuthNotification(JSON.parse(storedAuthNotification));

        if (authNotification === null) {
            sessionStorage.removeItem(storageKey);
        }

        return authNotification;
    } catch (error) {
        logException(error);

        try {
            sessionStorage.removeItem(storageKey);
        } catch (cleanupError) {
            logException(cleanupError);
        }

        return null;
    }
};

export const hasAuthNotificationFromPreviousDocument = (domainId: number): boolean => {
    if (!isClient || domainIdsWithNotificationStoredInCurrentDocument.has(domainId)) {
        return false;
    }

    try {
        return sessionStorage.getItem(getStorageKey(domainId)) !== null;
    } catch (error) {
        logException(error);

        return false;
    }
};

export const consumeAuthNotification = (domainId: number): AuthNotification | null => {
    if (!isClient) {
        return null;
    }

    const authNotification = getAuthNotification(domainId);

    try {
        sessionStorage.removeItem(getStorageKey(domainId));
        domainIdsWithNotificationStoredInCurrentDocument.delete(domainId);
    } catch (error) {
        logException(error);

        return null;
    }

    return authNotification;
};

const getStorageKey = (domainId: number): string => `${AUTH_NOTIFICATION_STORAGE_KEY}-${domainId}`;

const normalizeAuthNotification = (value: unknown): AuthNotification | null => {
    if (typeof value === 'string' && stringAuthNotifications.includes(value as AuthNotification)) {
        return value as AuthNotification;
    }

    const isSocialLoginFailNotification =
        typeof value === 'object' && value !== null && 'type' in value && value.type === 'social-login-fail';

    if (!isSocialLoginFailNotification) {
        return null;
    }

    const socialNetworkType =
        'socialNetworkType' in value && typeof value.socialNetworkType === 'string'
            ? getAllowedSocialNetworkType(value.socialNetworkType)
            : undefined;

    return { type: 'social-login-fail', socialNetworkType };
};
