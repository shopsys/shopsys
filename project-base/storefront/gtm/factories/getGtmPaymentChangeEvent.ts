import { SimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPaymentChangeEventType } from 'gtm/types/events';
import { GtmCartInfoType } from 'gtm/types/objects';

export const getGtmPaymentChangeEvent = (
    gtmCartInfo: GtmCartInfoType,
    updatedPayment: SimplePaymentFragment,
): GtmPaymentChangeEventType => ({
    event: GtmEventType.payment_change,
    ecommerce: {
        valueWithoutVat: gtmCartInfo.valueWithoutVat,
        valueWithVat: gtmCartInfo.valueWithVat,
        products: gtmCartInfo.products ?? [],
        currencyCode: gtmCartInfo.currencyCode,
        paymentType: updatedPayment.name,
        paymentPriceWithoutVat: parseFloat(updatedPayment.price.priceWithoutVat),
        paymentPriceWithVat: parseFloat(updatedPayment.price.priceWithVat),
        promoCodes: gtmCartInfo.promoCodes,
    },
    _clear: true,
});
