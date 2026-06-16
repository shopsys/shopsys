const ORDER_CONFIRMATION_CONTEXT_STORAGE_KEY = 'orderConfirmationContext';
const ORDER_CONFIRMATION_CONTEXT_TTL_IN_MS = 30 * 60 * 1000;

type OrderConfirmationContext = {
    orderUrlHash: string;
    createdAt: number;
    domainUrl: string;
};

const isOrderConfirmationContext = (value: unknown): value is OrderConfirmationContext => {
    return (
        typeof value === 'object' &&
        value !== null &&
        'orderUrlHash' in value &&
        'createdAt' in value &&
        'domainUrl' in value &&
        typeof value.orderUrlHash === 'string' &&
        typeof value.createdAt === 'number' &&
        typeof value.domainUrl === 'string'
    );
};

export const saveOrderConfirmationContext = (orderUrlHash: string, domainUrl: string): void => {
    sessionStorage.setItem(
        ORDER_CONFIRMATION_CONTEXT_STORAGE_KEY,
        JSON.stringify({
            orderUrlHash,
            createdAt: Date.now(),
            domainUrl,
        }),
    );
};

export const clearOrderConfirmationContext = (): void => {
    sessionStorage.removeItem(ORDER_CONFIRMATION_CONTEXT_STORAGE_KEY);
};

export const getValidOrderConfirmationContext = (domainUrl: string): OrderConfirmationContext | null => {
    const storedValue = sessionStorage.getItem(ORDER_CONFIRMATION_CONTEXT_STORAGE_KEY);

    if (storedValue === null) {
        return null;
    }

    try {
        const parsedValue: unknown = JSON.parse(storedValue);

        if (!isOrderConfirmationContext(parsedValue)) {
            clearOrderConfirmationContext();

            return null;
        }

        const isExpired = Date.now() - parsedValue.createdAt > ORDER_CONFIRMATION_CONTEXT_TTL_IN_MS;
        const isDifferentDomain = parsedValue.domainUrl !== domainUrl;

        if (isExpired || isDifferentDomain) {
            clearOrderConfirmationContext();

            return null;
        }

        return parsedValue;
    } catch {
        clearOrderConfirmationContext();

        return null;
    }
};
