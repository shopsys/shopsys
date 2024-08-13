import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmCartViewEventType } from 'gtm/types/events';
import { GtmCartItemType } from 'gtm/types/objects';

export const getGtmCartViewEvent = (
    currencyCode: string,
    valueWithoutVat: number | null,
    valueWithVat: number | null,
    products: GtmCartItemType[] | undefined,
    arePricesHidden: boolean,
): GtmCartViewEventType => ({
    event: GtmEventType.cart_view,
    ecommerce: {
        currencyCode,
        valueWithoutVat,
        valueWithVat,
        products,
        arePricesHidden,
    },
    _clear: true,
});
