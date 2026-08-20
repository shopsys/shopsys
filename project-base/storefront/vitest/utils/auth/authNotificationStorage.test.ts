import {
    consumeAuthNotification,
    getAuthNotification,
    storeAuthNotification,
} from 'utils/auth/authNotificationStorage';
import { beforeEach, describe, expect, test } from 'vitest';

describe('authNotificationStorage', () => {
    beforeEach(() => {
        sessionStorage.clear();
    });

    test('stores notifications per domain and consumes them exactly once', () => {
        storeAuthNotification(1, 'login');
        storeAuthNotification(2, 'logout');

        expect(consumeAuthNotification(1)).toBe('login');
        expect(consumeAuthNotification(1)).toBeNull();
        expect(consumeAuthNotification(2)).toBe('logout');
    });

    test('reads a notification without consuming it', () => {
        storeAuthNotification(1, 'registration');

        expect(getAuthNotification(1)).toBe('registration');
        expect(getAuthNotification(1)).toBe('registration');
        expect(consumeAuthNotification(1)).toBe('registration');
    });

    test('removes unsupported social network values from stored notifications', () => {
        sessionStorage.setItem(
            'shopsys-platform-auth-notification-1',
            JSON.stringify({ type: 'social-login-fail', socialNetworkType: '<img src=x onerror=alert(1)>' }),
        );

        expect(consumeAuthNotification(1)).toEqual({
            type: 'social-login-fail',
            socialNetworkType: undefined,
        });
    });

    test('discards invalid stored values when reading without consuming', () => {
        sessionStorage.setItem('shopsys-platform-auth-notification-1', JSON.stringify('unsupported-notification'));

        expect(getAuthNotification(1)).toBeNull();
        expect(sessionStorage.getItem('shopsys-platform-auth-notification-1')).toBeNull();
    });

    test('discards malformed stored values when reading without consuming', () => {
        sessionStorage.setItem('shopsys-platform-auth-notification-1', '{');

        expect(getAuthNotification(1)).toBeNull();
        expect(sessionStorage.getItem('shopsys-platform-auth-notification-1')).toBeNull();
    });
});
