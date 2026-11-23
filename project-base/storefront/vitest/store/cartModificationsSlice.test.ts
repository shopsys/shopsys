import { useSessionStore } from 'store/useSessionStore';
import { beforeEach, describe, expect, test } from 'vitest';
import { getCartModifications } from 'vitest/helpers/cartModificationsFixture';

const priceChange = getCartModifications({ transportModifications: { transportPriceChanged: true } });
const removedProduct = getCartModifications({ someProductWasRemovedFromEshop: true });

const receive = (cartUuid: string | null, modifications = priceChange) =>
    useSessionStore.getState().receiveCartModifications({ cartUuid, modifications, hasPayment: false });

const getKeptModifications = () =>
    useSessionStore.getState().cartModificationsBatches.map((batch) => batch.modifications);

describe('cart modifications slice', () => {
    beforeEach(() => {
        useSessionStore.setState({
            cartModificationsBatches: [],
            unhandledCartModificationsBatches: [],
            isOrderRejectedByCartModifications: false,
        });
    });

    test('keeps the refused order when the customer dismisses the modifications', () => {
        receive('cart-1');
        useSessionStore.getState().setIsOrderRejectedByCartModifications(true);

        useSessionStore.getState().dismissCartModifications();

        expect(useSessionStore.getState().isOrderRejectedByCartModifications).toBe(true);
    });

    test('keeps modifications of the same cart until the customer dismisses them', () => {
        receive('cart-1', priceChange);
        receive('cart-1', removedProduct);

        expect(getKeptModifications()).toEqual([priceChange, removedProduct]);

        useSessionStore.getState().dismissCartModifications();

        expect(getKeptModifications()).toEqual([]);
    });

    test('replaces kept modifications with modifications of a different cart', () => {
        receive('cart-1', priceChange);
        receive('cart-2', removedProduct);

        expect(getKeptModifications()).toEqual([removedProduct]);
    });

    test('clears kept and unhandled modifications and the refused order', () => {
        receive('cart-1');
        useSessionStore.getState().setIsOrderRejectedByCartModifications(true);

        useSessionStore.getState().clearCartModifications();

        expect(getKeptModifications()).toEqual([]);
        expect(useSessionStore.getState().takeUnhandledCartModificationsBatches()).toEqual([]);
        expect(useSessionStore.getState().isOrderRejectedByCartModifications).toBe(false);
    });

    test('hands every received batch over for handling exactly once', () => {
        receive('cart-1');
        receive('cart-1', removedProduct);

        expect(
            useSessionStore
                .getState()
                .takeUnhandledCartModificationsBatches()
                .map((batch) => batch.modifications),
        ).toEqual([priceChange, removedProduct]);
        expect(useSessionStore.getState().takeUnhandledCartModificationsBatches()).toEqual([]);
    });
});
