import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { GtmPaymentChangeEventType } from 'gtm/types/events';
import { GtmCartInfoType } from 'gtm/types/objects';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';

export const getGtmPaymentChangeEvent = (
    gtmCartInfo: GtmCartInfoType,
    updatedPayment: TypeSimplePaymentFragment,
    arePricesHidden: boolean,
): GtmPaymentChangeEventType => ({
    event: GtmEventType.payment_change,
    ecommerce: {
        valueWithoutVat: gtmCartInfo.valueWithoutVat,
        valueWithVat: gtmCartInfo.valueWithVat,
        products: gtmCartInfo.products ?? [],
        currencyCode: gtmCartInfo.currencyCode,
        paymentType: updatedPayment.name,
        paymentPriceWithoutVat: getGtmPriceBasedOnVisibility(updatedPayment.price.priceWithoutVat),
        paymentPriceWithVat: getGtmPriceBasedOnVisibility(updatedPayment.price.priceWithVat),
        promoCodes: gtmCartInfo.promoCodes,
        arePricesHidden,
    },
    _clear: true,
});
