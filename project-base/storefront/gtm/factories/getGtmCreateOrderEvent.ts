import { TypeCartFragment } from 'graphql/requests/cart/fragments/CartFragment.generated';
import { TypeSimplePaymentFragment } from 'graphql/requests/payments/fragments/SimplePaymentFragment.generated';
import { GtmEventType } from 'gtm/enums/GtmEventType';
import { mapGtmCartItemType } from 'gtm/mappers/mapGtmCartItemType';
import { GtmCreateOrderEventOrderPartType, GtmCreateOrderEventType } from 'gtm/types/events';
import { GtmUserInfoType, GtmReviewConsentsType } from 'gtm/types/objects';
import { getGtmPriceBasedOnVisibility } from 'gtm/utils/getGtmPriceBasedOnVisibility';
import { getGtmUserInfo } from 'gtm/utils/getGtmUserInfo';
import { ContactInformation } from 'store/slices/createContactInformationSlice';
import { CurrentCustomerType } from 'types/customer';
import { DomainConfigType } from 'utils/domain/domainConfig';

export const getGtmCreateOrderEvent = (
    gtmCreateOrderEventOrderPart: GtmCreateOrderEventOrderPartType,
    gtmCreateOrderEventUserPart: GtmUserInfoType,
    arePricesHidden: boolean,
    isPaymentSuccessful?: boolean,
): GtmCreateOrderEventType => ({
    event: GtmEventType.create_order,
    ecommerce: {
        ...gtmCreateOrderEventOrderPart,
        isPaymentSuccessful,
        arePricesHidden,
    },
    user: gtmCreateOrderEventUserPart,
    _clear: true,
});

export const getGtmCreateOrderEventOrderPart = (
    cart: TypeCartFragment,
    payment: TypeSimplePaymentFragment,
    promoCode: string | null,
    orderNumber: string,
    reviewConsents: GtmReviewConsentsType,
    domainConfig: DomainConfigType,
): GtmCreateOrderEventOrderPartType => ({
    currencyCode: domainConfig.currencyCode,
    id: orderNumber,
    valueWithoutVat: getGtmPriceBasedOnVisibility(cart.totalPrice.priceWithoutVat),
    valueWithVat: getGtmPriceBasedOnVisibility(cart.totalPrice.priceWithVat),
    vatAmount: parseFloat(cart.totalPrice.vatAmount),
    paymentPriceWithoutVat: getGtmPriceBasedOnVisibility(payment.price.priceWithoutVat),
    paymentPriceWithVat: getGtmPriceBasedOnVisibility(payment.price.priceWithVat),
    promoCodes: promoCode !== null ? [promoCode] : undefined,
    paymentType: payment.name,
    reviewConsents,
    products: cart.items.map((cartItem, index) => mapGtmCartItemType(cartItem, domainConfig.url, index)),
});

export const getGtmCreateOrderEventUserPart = (
    user: CurrentCustomerType | null | undefined,
    userContactInformation: ContactInformation,
): GtmUserInfoType => getGtmUserInfo(user, userContactInformation);
