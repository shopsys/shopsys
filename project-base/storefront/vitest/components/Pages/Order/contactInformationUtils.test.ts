import { getShouldShowOrderNotSubmittedAlert } from 'components/Pages/Order/ContactInformation/contactInformationUtils';
import { TypeCartModificationsFragment } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';
import { TypeCreateOrderMutation } from 'graphql/requests/orders/mutations/CreateOrderMutation.generated';
import { describe, expect, test } from 'vitest';
import { getCartModifications } from 'vitest/helpers/cartModificationsFixture';

const priceChange = getCartModifications({ transportModifications: { transportPriceChanged: true } });

const getCreateOrderData = (
    orderCreated: boolean,
    modifications: TypeCartModificationsFragment | null,
    { transport = {}, payment = {} }: { transport?: object | null; payment?: object | null } = {},
) =>
    ({
        CreateOrder: {
            orderCreated,
            order: null,
            cart: modifications ? { modifications, items: [{}], transport, payment } : null,
        },
    }) as TypeCreateOrderMutation;

describe('getShouldShowOrderNotSubmittedAlert', () => {
    test('shows the alert when the API refused the order because of cart modifications', () => {
        expect(getShouldShowOrderNotSubmittedAlert(getCreateOrderData(false, priceChange))).toBe(true);
    });

    test.each([
        ['the API created the order', getCreateOrderData(true, priceChange)],
        ['the API refused the order without returning a cart', getCreateOrderData(false, null)],
        ['the returned cart has no modification', getCreateOrderData(false, getCartModifications())],
        [
            'the checkout redirects because the transport was removed',
            getCreateOrderData(false, priceChange, { transport: null }),
        ],
        ['the request failed without data', undefined],
    ])('does not show the alert when %s', (_, createOrderData) => {
        expect(getShouldShowOrderNotSubmittedAlert(createOrderData)).toBe(false);
    });
});
