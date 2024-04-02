import { CartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { SimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { mapGtmCartItemType } from 'gtm/mappers/mapGtmCartItemType';
import { GtmCreateOrderEventOrderPartType, GtmCreateOrderEventType } from 'gtm/types/events';
import { GtmUserInfoType, GtmReviewConsentsType } from 'gtm/types/objects';
import { getGtmUserInfo } from 'gtm/utils/getGtmUserInfo';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { CurrentCustomerType } from 'types/customer';
import { DomainConfigType } from 'utils/domain/domainConfig';
import { mapPriceForCalculations } from 'utils/mappers/price';

export const getGtmCreateOrderEvent = (
    gtmCreateOrderEventOrderPart: GtmCreateOrderEventOrderPartType,
    gtmCreateOrderEventUserPart: GtmUserInfoType,
    isPaymentSuccessful?: boolean,
): GtmCreateOrderEventType => ({
    event: GtmEventType.create_order,
    ecommerce: {
        ...gtmCreateOrderEventOrderPart,
        isPaymentSuccessful,
    },
    user: gtmCreateOrderEventUserPart,
    _clear: true,
});

export const getGtmCreateOrderEventOrderPart = (
    cart: CartFragment,
    payment: SimplePaymentFragment,
    promoCode: string | null,
    orderNumber: string,
    reviewConsents: GtmReviewConsentsType,
    domainConfig: DomainConfigType,
): GtmCreateOrderEventOrderPartType => ({
    currencyCode: domainConfig.currencyCode,
    id: orderNumber,
    valueWithoutVat: parseFloat(cart.totalPrice.priceWithoutVat),
    valueWithVat: parseFloat(cart.totalPrice.priceWithVat),
    vatAmount: parseFloat(cart.totalPrice.vatAmount),
    paymentPriceWithoutVat: mapPriceForCalculations(payment.price.priceWithoutVat),
    paymentPriceWithVat: mapPriceForCalculations(payment.price.priceWithVat),
    promoCodes: promoCode !== null ? [promoCode] : undefined,
    paymentType: payment.name,
    reviewConsents,
    products: cart.items.map((cartItem, index) => mapGtmCartItemType(cartItem, domainConfig.url, index)),
});

export const getGtmCreateOrderEventUserPart = (
    user: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
): GtmUserInfoType => getGtmUserInfo(user, userContactInformation);
