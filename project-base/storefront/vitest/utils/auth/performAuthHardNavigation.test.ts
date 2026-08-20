import { performAuthHardNavigation } from 'utils/auth/performAuthHardNavigation';
import { afterAll, beforeEach, describe, expect, test, vi } from 'vitest';

const originalWindowLocation = window.location;
const reloadMock = vi.fn();
const replaceMock = vi.fn();

describe('performAuthHardNavigation', () => {
    beforeEach(() => {
        Object.defineProperty(window, 'location', {
            configurable: true,
            value: {
                origin: 'https://example.com',
                reload: reloadMock,
                replace: replaceMock,
            },
        });
    });

    afterAll(() => {
        Object.defineProperty(window, 'location', {
            configurable: true,
            value: originalWindowLocation,
        });
    });

    test('reloads the current page when no URL is provided', () => {
        performAuthHardNavigation();

        expect(reloadMock).toHaveBeenCalledOnce();
        expect(replaceMock).not.toHaveBeenCalled();
    });

    test.each([
        ['/customer', 'https://example.com/customer'],
        ['/customer/orders?status=new#latest', 'https://example.com/customer/orders?status=new#latest'],
        ['customer', 'https://example.com/customer'],
        ['https://example.com/customer', 'https://example.com/customer'],
    ])('navigates to same-origin URL %s', (url, expectedUrl) => {
        performAuthHardNavigation(url);

        expect(replaceMock).toHaveBeenCalledOnce();
        expect(replaceMock).toHaveBeenCalledWith(expectedUrl);
    });

    test.each([
        'https://attacker.example/customer',
        'http://example.com/customer',
        '//attacker.example/customer',
        '/\\attacker.example/customer',
        'https://example.com//attacker.example/customer',
        '/..//attacker.example/customer',
        '/\\/attacker.example/customer',
        'javascript:alert(document.domain)',
        'data:text/html,malicious',
        'http://[',
    ])('navigates to the homepage instead of unsafe URL %s', (url) => {
        performAuthHardNavigation(url);

        expect(replaceMock).toHaveBeenCalledOnce();
        expect(replaceMock).toHaveBeenCalledWith('/');
    });
});
