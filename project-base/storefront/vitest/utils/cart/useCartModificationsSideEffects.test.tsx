import { act, renderHook } from '@testing-library/react';
import { StrictMode } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { useCartModificationsSideEffects } from 'utils/cart/useCartModificationsSideEffects';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { getCartModifications } from 'vitest/helpers/cartModificationsFixture';

const { changePaymentInCartMock, showMessageEventMock } = vi.hoisted(() => ({
    changePaymentInCartMock: vi.fn(),
    showMessageEventMock: vi.fn(),
}));

vi.mock('utils/cart/useChangePaymentInCart', () => ({
    useChangePaymentInCart: () => ({ changePaymentInCart: changePaymentInCartMock }),
}));
vi.mock('gtm/handlers/onGtmShowMessageEventHandler', () => ({ onGtmShowMessageEventHandler: showMessageEventMock }));
vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));

const unavailableTransport = getCartModifications({ transportModifications: { transportUnavailable: true } });
const priceChange = getCartModifications({ transportModifications: { transportPriceChanged: true } });

const receive = (modifications = priceChange, hasPayment = false) =>
    act(() => useSessionStore.getState().receiveCartModifications({ cartUuid: 'cart-1', modifications, hasPayment }));

describe('useCartModificationsSideEffects', () => {
    beforeEach(() => {
        changePaymentInCartMock.mockClear();
        showMessageEventMock.mockClear();
        useSessionStore.setState({
            cartModificationsBatches: [],
            unhandledCartModificationsBatches: [],
            isOrderRejectedByCartModifications: false,
        });
    });

    test('reports each batch once, including a batch received before the deferred hook mounted', () => {
        receive();
        renderHook(useCartModificationsSideEffects, { wrapper: StrictMode });
        receive();

        expect(showMessageEventMock).toHaveBeenCalledTimes(2);
        expect(showMessageEventMock).toHaveBeenCalledWith(
            'information',
            'The price of the transport you selected has changed.',
            'flash message',
            'cart',
        );
    });

    test('does not report a batch again when the customer dismisses the modifications', () => {
        renderHook(useCartModificationsSideEffects);
        receive();

        act(() => useSessionStore.getState().dismissCartModifications());

        expect(showMessageEventMock).toHaveBeenCalledOnce();
    });

    test('removes the payment of the reported cart once when the API removed its transport and kept its payment', () => {
        renderHook(useCartModificationsSideEffects);

        receive(unavailableTransport, true);
        receive(unavailableTransport, false);

        expect(changePaymentInCartMock).toHaveBeenCalledOnce();
        expect(changePaymentInCartMock).toHaveBeenCalledWith(null, null, 'cart-1');
    });
});
