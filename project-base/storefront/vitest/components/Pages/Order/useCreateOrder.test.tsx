import { act, renderHook } from '@testing-library/react';
import { useCreateOrder } from 'components/Pages/Order/ContactInformation/contactInformationUtils';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { useSessionStore } from 'store/useSessionStore';
import { beforeEach, expect, test, vi } from 'vitest';

const { createOrderMutationMock } = vi.hoisted(() => ({ createOrderMutationMock: vi.fn() }));

vi.mock('graphql/requests/orders/mutations/CreateOrderMutation.generated', () => ({
    useCreateOrderMutation: () => [{}, createOrderMutationMock],
}));
vi.mock('graphql/requests/settings/queries/SettingsQuery.generated', () => ({
    useSettingsQuery: () => [{ data: undefined }],
}));
vi.mock('next/router', () => ({
    useRouter: () => ({ events: { on: vi.fn(), off: vi.fn() }, replace: vi.fn().mockResolvedValue(true) }),
}));
vi.mock('store/usePersistStore', () => ({
    usePersistStore: (selector: (state: unknown) => unknown) =>
        selector({ cartUuid: 'cart-1', updateCartUuid: vi.fn(), resetContactInformation: vi.fn() }),
}));
vi.mock('utils/cart/useCurrentCart', () => ({
    useCurrentCart: () => ({ cart: null, transport: null, payment: null, pickupPlace: null, promoCodes: [] }),
}));
vi.mock('connectors/customer/CurrentCustomer', () => ({ useCurrentCustomerData: () => null }));
vi.mock('components/providers/DomainConfigProvider', () => ({
    useDomainConfig: () => ({ url: 'http://localhost/', domainId: 1 }),
}));
vi.mock('components/providers/AuthorizationProvider', () => ({ useAuthorization: () => ({ canSeePrices: true }) }));
vi.mock('gtm/context/GtmProvider', () => ({ useGtmContext: () => ({ ipAddress: null }) }));
vi.mock('utils/errors/useErrorHandler', () => ({ useErrorHandler: () => vi.fn() }));

const formValues = {
    country: { value: 'CZ', label: 'Czechia' },
    customer: 'privateCustomer',
    email: 'customer@example.com',
    isDeliveryAddressDifferentFromBilling: false,
    isWithoutHeurekaAgreement: false,
    telephone: '123456789',
    telephonePrefix: '+420',
    telephonePrefixCountryCode: 'CZ',
} as unknown as ContactInformation;

const refusedOrder = { data: { CreateOrder: { orderCreated: false, order: null, cart: null } } };

const renderCreateOrder = () =>
    renderHook(() => useCreateOrder({} as never, { messages: { error: 'Order could not be created' } } as never));

beforeEach(() => {
    createOrderMutationMock.mockReset();
    useSessionStore.setState({ isOrderBeingCreated: false, isPageLoading: false });
});

test('never starts a second order while the first one is being created, even from a page visited again', async () => {
    let answerFirstOrder = (_result: typeof refusedOrder): void => undefined;
    const waitForFirstOrderResponse = () =>
        new Promise((resolve) => {
            answerFirstOrder = resolve;
        });
    createOrderMutationMock.mockImplementationOnce(waitForFirstOrderResponse).mockResolvedValue(refusedOrder);

    const firstPageVisit = renderCreateOrder();
    let firstSubmission: Promise<void> | undefined;
    const submitFirstOrder = () => {
        firstSubmission = firstPageVisit.result.current.createOrder(formValues) as Promise<void>;
    };
    act(submitFirstOrder);
    firstPageVisit.unmount();

    const secondPageVisit = renderCreateOrder();
    await act(() => secondPageVisit.result.current.createOrder(formValues));

    expect(createOrderMutationMock).toHaveBeenCalledOnce();
    expect(useSessionStore.getState().isOrderBeingCreated).toBe(true);

    const resolveFirstOrderAndWait = async () => {
        answerFirstOrder(refusedOrder);
        await firstSubmission;
    };
    await act(resolveFirstOrderAndWait);
    await act(() => secondPageVisit.result.current.createOrder(formValues));

    expect(createOrderMutationMock).toHaveBeenCalledTimes(2);
    expect(useSessionStore.getState().isOrderBeingCreated).toBe(false);
});
