// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { OrderDetailFragment } from '../fragments/OrderDetailFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Represents the status of the order confirmation page content. */
export type TypeOrderConfirmationPageContentStatusEnum =
  | 'FAILED'
  | 'IN_PROCESS'
  | 'SUCCESSFUL';

/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'additionalService'
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

/** Status of order */
export type TypeOrderStatusEnum =
  /** Canceled */
  | 'canceled'
  /** Done */
  | 'done'
  /** In progress */
  | 'inProgress'
  /** New */
  | 'new'
  /** Withdrawn */
  | 'withdrawn';

/** One of possible product types */
export type TypeProductTypeEnum =
  /** Basic product */
  | 'BASIC'
  /** Gift voucher delivered by email after the order is paid */
  | 'ELECTRONIC_GIFT_VOUCHER'
  /** Product with inquiry form instead of add to cart button */
  | 'INQUIRY'
  /** Gift voucher delivered printed as a regular product */
  | 'PRINTED_GIFT_VOUCHER';

/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'email'
  | 'packetery'
  | 'personal_pickup';

export type TypeOrderDetailByHashQueryVariables = Exact<{
  urlHash?: string | null | undefined;
}>;


export type TypeOrderDetailByHashQuery = { order: { __typename: 'Order', uuid: string, number: string, creationDate: string, expectedDeliveryDate: string | null, status: string, statusType: Types.TypeOrderStatusEnum, firstName: string | null, lastName: string | null, email: string, telephone: string, companyName: string | null, companyNumber: string | null, companyTaxNumber: string | null, street: string, city: string, postcode: string, isDeliveryAddressDifferentFromBilling: boolean, deliveryFirstName: string | null, deliveryLastName: string | null, deliveryCompanyName: string | null, deliveryTelephone: string | null, deliveryStreet: string | null, deliveryCity: string | null, deliveryPostcode: string | null, note: string | null, urlHash: string, promoCode: string | null, trackingNumber: string | null, trackingUrl: string | null, remainingAmountToPay: string, isPaid: boolean, hasExternalPayment: boolean, hasPaymentInProcess: boolean, isAwaitingPayment: boolean, paymentTransactionsCount: number, lastExternalPaymentUrl: string | null, paymentStatus: string | null, deliveredAt: string | null, canRequestWithdrawal: boolean, isWithdrawalBlockedByPurchasedGiftVoucher: boolean, withdrawalDeadline: string | null, productReviewsAllowed: boolean, reviewedProductUuids: Array<string>, items: Array<{ __typename: 'OrderItem', uuid: string, name: string, catnum: string | null, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, relatedItems: Array<{ __typename: 'OrderItem', uuid: string, name: string, catnum: string | null, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, deliveryDaysExtension: number | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, order: { uuid: string, number: string, creationDate: string, customerUser:
          | { uuid: string }
          | { uuid: string }
          | { uuid: string }
          | { uuid: string }
         | null, withdrawalRequest: { __typename: 'OrderWithdrawalRequest' } | null }, product:
        | { fullName: string, uuid: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, productType: Types.TypeProductTypeEnum, isCurrentlyOutOfStock: boolean, mainCategory: { name: string } | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
        | { fullName: string, uuid: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, productType: Types.TypeProductTypeEnum, isCurrentlyOutOfStock: boolean, mainCategory: { name: string } | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
        | { fullName: string, uuid: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, productType: Types.TypeProductTypeEnum, isCurrentlyOutOfStock: boolean, mainCategory: { name: string } | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
       | null, transport: { name: string, transportTypeCode: Types.TypeTransportTypeEnum, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null, payment: { name: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null }>, country: { __typename: 'Country', name: string, code: string }, deliveryCountry: { __typename: 'Country', name: string, code: string } | null, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, giftVouchers: Array<{ __typename: 'AppliedGiftVoucher', code: string, valueWithVat: string, valueWithoutVat: string, productName: string | null }>, purchasedGiftVouchers: Array<{ productCatnum: string | null, pdfUrl: string }>, confirmationPageContent: { content: string, status: Types.TypeOrderConfirmationPageContentStatusEnum }, withdrawalRequest: { __typename: 'OrderWithdrawalRequest', email: string, firstName: string, lastName: string, telephone: string | null, note: string | null, requestedAt: string, confirmed: boolean } | null, customerUser:
      | { uuid: string }
      | { uuid: string }
      | { uuid: string }
      | { uuid: string }
     | null } | null };


export const OrderDetailByHashQueryDocument = gql`
    query OrderDetailByHashQuery($urlHash: String) {
  order(urlHash: $urlHash) {
    ...OrderDetailFragment
  }
}
    ${OrderDetailFragment}`;

export function useOrderDetailByHashQuery(options?: Omit<Urql.UseQueryArgs<TypeOrderDetailByHashQueryVariables>, 'query'>) {
  return Urql.useQuery<TypeOrderDetailByHashQuery, TypeOrderDetailByHashQueryVariables>({ query: OrderDetailByHashQueryDocument, ...options });
};