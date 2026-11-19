import { hasCartModifications } from 'connectors/cart/Cart';
import { TypeCartModificationsFragment } from 'graphql/requests/cart/fragments/CartModificationsFragment.generated';
import { useSessionStore } from 'store/useSessionStore';
import { type Exchange, makeOperation, type Operation, type OperationResult } from 'urql';
import { map, pipe, tap } from 'wonka';

type CartWithModifications = {
    __typename: 'Cart';
    uuid: string | null;
    modifications: TypeCartModificationsFragment;
    payment?: unknown;
};

const isObject = (value: unknown): value is object => typeof value === 'object' && value !== null;

const isCartWithModifications = (value: unknown): value is CartWithModifications =>
    isObject(value) &&
    '__typename' in value &&
    value.__typename === 'Cart' &&
    'uuid' in value &&
    (typeof value.uuid === 'string' || value.uuid === null) &&
    'modifications' in value &&
    isObject(value.modifications);

const isCreatedOrder = (responseData: unknown): boolean =>
    isObject(responseData) &&
    'CreateOrder' in responseData &&
    isObject(responseData.CreateOrder) &&
    'orderCreated' in responseData.CreateOrder &&
    responseData.CreateOrder.orderCreated === true;

// A cart is either a root field (CartQuery, cart mutations) or a field of a root result (AddToCart, CreateOrder)
const getCartsWithModifications = (responseData: unknown): CartWithModifications[] => {
    if (!isObject(responseData)) {
        return [];
    }

    const rootFields = Object.values(responseData);
    const rootFieldChildren = rootFields.filter(isObject).flatMap((rootField) => Object.values(rootField));

    return [...rootFields, ...rootFieldChildren].filter(isCartWithModifications);
};

const receiveCartModifications = (responseData: unknown) => {
    for (const cart of getCartsWithModifications(responseData)) {
        if (hasCartModifications(cart.modifications)) {
            useSessionStore.getState().receiveCartModifications({
                cartUuid: cart.uuid,
                modifications: cart.modifications,
                hasPayment: !!cart.payment,
            });
        }
    }
};

/**
 * The API reports a cart modification only once, in the network response that detected it.
 * This exchange sits below the cache, so it sees only network responses and never a cache read
 * or an optimistic result that repeats an older modification.
 *
 * Responses of requests sent before an order was created belong to the ordered cart, so their modifications
 * are ignored. The requests are told apart by the order in which they were sent, because the cart of
 * a logged-in customer has no UUID.
 */
export const cartModificationsExchange: Exchange = ({ forward }) => {
    let lastRequestNumber = 0;
    let lastRequestNumberBeforeOrder = 0;

    // Every execution gets its own number, because identical requests share the same operation key
    const numberRequest = (operation: Operation): Operation => {
        if (operation.kind === 'teardown') {
            return operation;
        }

        lastRequestNumber += 1;

        return makeOperation(operation.kind, operation, {
            ...operation.context,
            cartModificationsRequestNumber: lastRequestNumber,
        });
    };

    const handleResult = ({ operation, data: responseData }: OperationResult) => {
        const requestNumber: number = operation.context.cartModificationsRequestNumber;
        const isResponseOfOrderedCart = requestNumber <= lastRequestNumberBeforeOrder;

        if (isCreatedOrder(responseData)) {
            lastRequestNumberBeforeOrder = lastRequestNumber;
            useSessionStore.getState().clearCartModifications();
        } else if (!isResponseOfOrderedCart) {
            receiveCartModifications(responseData);
        }
    };

    return (operations$) => pipe(operations$, map(numberRequest), forward, tap(handleResult));
};
