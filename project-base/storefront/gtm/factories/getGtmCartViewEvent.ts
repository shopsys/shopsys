import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmCartViewEventType } from 'gtm/types/events';
import { GtmCartItemType } from 'gtm/types/objects';

export const getGtmCartViewEvent = (
    currencyCode: string,
    valueWithoutVat: number,
    valueWithVat: number,
    products: GtmCartItemType[] | undefined,
): GtmCartViewEventType => ({
    event: GtmEventType.cart_view,
    ecommerce: {
        currencyCode,
        valueWithoutVat,
        valueWithVat,
        products,
    },
    _clear: true,
});
