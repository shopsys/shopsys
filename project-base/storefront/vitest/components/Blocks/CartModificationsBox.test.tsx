import { act, render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { CartModificationsBox } from 'components/Blocks/CartModificationsBox/CartModificationsBox';
import { OrderNotSubmittedAlert } from 'components/Pages/Order/ContactInformation/OrderNotSubmittedAlert';
import { ReactNode } from 'react';
import { useSessionStore } from 'store/useSessionStore';
import { beforeEach, describe, expect, test, vi } from 'vitest';
import { getCartModifications } from 'vitest/helpers/cartModificationsFixture';

vi.mock('utils/i18n/useTranslationWrapper', () => ({ default: () => ({ t: (key: string) => key }) }));

const priceChange = getCartModifications({ transportModifications: { transportPriceChanged: true } });
const removedProduct = getCartModifications({ someProductWasRemovedFromEshop: true });

const receive = (modifications = priceChange) =>
    act(() =>
        useSessionStore.getState().receiveCartModifications({ cartUuid: 'cart-1', modifications, hasPayment: false }),
    );

const renderInPage = (content: ReactNode) =>
    render(
        <main>
            <h1>Contact information</h1>
            {content}
        </main>,
    );

describe('CartModificationsBox', () => {
    beforeEach(() => {
        useSessionStore.setState({
            cartModificationsBatches: [],
            unhandledCartModificationsBatches: [],
            isOrderRejectedByCartModifications: false,
        });
    });

    test('lists every modification once inside a live region until the customer dismisses them', async () => {
        const user = userEvent.setup();
        renderInPage(<CartModificationsBox />);

        const liveRegion = screen.getByRole('status');
        expect(liveRegion).toBeEmptyDOMElement();

        receive(priceChange);
        receive(removedProduct);
        receive(priceChange);

        expect(liveRegion).toHaveTextContent('Your cart has changed');
        expect(screen.getAllByRole('listitem').map((item) => item.textContent)).toEqual([
            'The price of the transport you selected has changed.',
            'Some product was removed from e-shop and your cart was recalculated.',
        ]);

        await user.click(screen.getByRole('button', { name: 'Close' }));

        expect(liveRegion).toBeEmptyDOMElement();
        expect(screen.getByRole('heading', { name: 'Contact information' })).toHaveFocus();

        receive(removedProduct);

        expect(liveRegion).toHaveTextContent('Some product was removed from e-shop and your cart was recalculated.');
        expect(screen.queryByRole('list')).not.toBeInTheDocument();
    });

    test('keeps the focus in place of the box when the page has no heading', async () => {
        const user = userEvent.setup();
        render(<CartModificationsBox />);
        receive(priceChange);

        await user.click(screen.getByRole('button', { name: 'Close' }));

        expect(screen.getByRole('status')).toHaveFocus();
    });
});

describe('OrderNotSubmittedAlert', () => {
    beforeEach(() => {
        useSessionStore.setState({
            cartModificationsBatches: [],
            unhandledCartModificationsBatches: [],
            isOrderRejectedByCartModifications: false,
        });
    });

    test('takes the focus and moves it to the cart modifications on request', async () => {
        const user = userEvent.setup();
        receive(priceChange);
        renderInPage(
            <>
                <CartModificationsBox />
                <OrderNotSubmittedAlert />
            </>,
        );

        expect(screen.getByText('The order was not submitted').closest('[tabindex="-1"]')).toHaveFocus();

        await user.click(screen.getByRole('button', { name: 'Show changes' }));

        expect(screen.getByRole('status')).toHaveFocus();
    });

    test('stays without the link to the changes after the customer dismisses them', async () => {
        const user = userEvent.setup();
        receive(priceChange);
        renderInPage(
            <>
                <CartModificationsBox />
                <OrderNotSubmittedAlert />
            </>,
        );

        await user.click(screen.getByRole('button', { name: 'Close' }));

        expect(screen.getByText('The order was not submitted')).toBeInTheDocument();
        expect(screen.queryByRole('button', { name: 'Show changes' })).not.toBeInTheDocument();
    });
});
