// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CartFragment } from '../../cart/fragments/CartFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product availability status in stock */
  | 'InStock'
  /** Product availability status out of stock */
  | 'OutOfStock';

/** One of possible types of the cart item */
export type TypeCartItemTypeEnum =
  | 'product'
  | 'productGift';

/** One of possible types of the order item */
export type TypeOrderItemTypeEnum =
  | 'discount'
  | 'payment'
  | 'product'
  | 'productGift'
  | 'promotion'
  | 'rounding'
  | 'transport';

/** Represents the type of the parameter */
export type TypeParameterTypeEnum =
  | 'CHECKBOX'
  | 'COLOR'
  | 'SLIDER';

/** One of the possible methods of the payment type */
export type TypePaymentTypeEnum =
  | 'bankTransfer'
  | 'basic'
  | 'goPay';

/** Represents phone number input */
export type TypePhoneDataInput = {
  /** Phone prefix country code in ISO 3166-1 alpha-2 */
  countryCode: string;
  /** Phone number without prefix */
  number: string;
  /** Phone prefix (eg. +420) */
  prefix: string;
};

/** One of possible promo code types */
export type TypePromoCodeTypeEnum =
  /** Discount type free transport and payment */
  | 'free_transport_payment'
  /** Discount type nominal */
  | 'nominal'
  /** Discount type percent */
  | 'percent';

/** Status of store opening */
export type TypeStoreOpeningStatusEnum =
  /** Store is currently closed */
  | 'CLOSED'
  /** Store will be closed soon */
  | 'CLOSED_SOON'
  /** Store is currently opened */
  | 'OPEN'
  /** Store will be opened soon */
  | 'OPEN_SOON';

/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'packetery'
  | 'personal_pickup';

/** Reason why a transport cannot be selected for the given cart */
export type TypeTransportUnavailabilityReasonInCartEnum =
  | 'excluded_for_product'
  | 'personal_pickup_required';

export type TypeCreateOrderMutationVariables = Exact<{
  firstName: string;
  lastName: string;
  email: string;
  telephone: Types.TypePhoneDataInput;
  onCompanyBehalf: boolean;
  companyName?: string | null | undefined;
  companyNumber?: string | null | undefined;
  companyTaxNumber?: string | null | undefined;
  street: string;
  city: string;
  postcode: string;
  country: string;
  isDeliveryAddressDifferentFromBilling: boolean;
  deliveryFirstName?: string | null | undefined;
  deliveryLastName?: string | null | undefined;
  deliveryCompanyName?: string | null | undefined;
  deliveryTelephone?: Types.TypePhoneDataInput | null | undefined;
  deliveryStreet?: string | null | undefined;
  deliveryCity?: string | null | undefined;
  deliveryPostcode?: string | null | undefined;
  deliveryCountry?: string | null | undefined;
  deliveryAddressUuid?: string | null | undefined;
  note?: string | null | undefined;
  cartUuid?: string | null | undefined;
  newsletterSubscription?: boolean | null | undefined;
  heurekaAgreement: boolean;
}>;


export type TypeCreateOrderMutation = { CreateOrder: { orderCreated: boolean, order: { number: string, uuid: string, urlHash: string, items: Array<{ type: Types.TypeOrderItemTypeEnum, payment: { type: Types.TypePaymentTypeEnum } | null }> } | null, cart: { __typename: 'Cart', uuid: string | null, remainingAmountForFreeTransport: string | null, selectedPickupPlaceIdentifier: string | null, paymentGoPayBankSwift: string | null, items: Array<{ __typename: 'CartItem', uuid: string, quantity: number, type: Types.TypeCartItemTypeEnum, freeQuantity: number, product:
          | { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
          | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
          | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, mainVariant: { slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
         }>, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalItemsPriceBeforeDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalProductPriceAdjustmentsDiscount: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalDiscountPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, modifications: { __typename: 'CartModificationsResult', someProductWasRemovedFromEshop: boolean, itemModifications: { __typename: 'CartItemModificationsResult', noLongerListableCartItems: Array<{ __typename: 'CartItem', uuid: string, quantity: number, type: Types.TypeCartItemTypeEnum, freeQuantity: number, product:
              | { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
              | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
              | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, mainVariant: { slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
             }>, cartItemsWithModifiedPrice: Array<{ __typename: 'CartItem', uuid: string, quantity: number, type: Types.TypeCartItemTypeEnum, freeQuantity: number, product:
              | { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
              | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
              | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, mainVariant: { slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
             }>, cartItemsWithChangedQuantity: Array<{ __typename: 'CartItem', uuid: string, quantity: number, type: Types.TypeCartItemTypeEnum, freeQuantity: number, product:
              | { __typename: 'MainVariant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
              | { __typename: 'RegularProduct', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
              | { __typename: 'Variant', id: number, uuid: string, slug: string, fullName: string, catalogNumber: string, isInquiryType: boolean, stockQuantity: number | null, isAllowedNegativeStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, availableStoresCount: number | null, vatPercent: string, mainVariant: { slug: string } | null, flags: Array<{ __typename: 'Flag', uuid: string, name: string, rgbColor: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, availability: { __typename: 'Availability', name: string, status: Types.TypeAvailabilityStatusEnum }, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, unit: { name: string }, brand: { __typename: 'Brand', name: string, slug: string } | null, categories: Array<{ name: string }>, parameters: Array<{ __typename: 'Parameter', uuid: string, name: string, type: Types.TypeParameterTypeEnum, group: string | null, unit: { __typename: 'Unit', name: string } | null, values: Array<{ __typename: 'ParameterValue', uuid: string, text: string, rgbHex: string | null, colorIcon: { url: string, anchorText: string } | null }> }> }
             }> }, transportModifications: { __typename: 'CartTransportModificationsResult', transportPriceChanged: boolean, transportUnavailable: boolean, transportWeightLimitExceeded: boolean, personalPickupStoreUnavailable: boolean }, paymentModifications: { __typename: 'CartPaymentModificationsResult', paymentPriceChanged: boolean, paymentUnavailable: boolean }, promoCodeModifications: { __typename: 'CartPromoCodeModificationsResult', noLongerApplicablePromoCode: Array<string> }, multipleAddedProductModifications: { notAddedProducts: Array<
            | { fullName: string }
            | { fullName: string }
            | { fullName: string }
          > } }, transport: { __typename: 'Transport', uuid: string, name: string, description: string | null, daysUntilDelivery: number, transportTypeCode: Types.TypeTransportTypeEnum, isPersonalPickup: boolean, vatPercent: string, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, payments: Array<{ __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null }>, stores: { __typename: 'StoreConnection', edges: Array<{ __typename: 'StoreEdge', node: { __typename: 'Store', slug: string, name: string, description: string | null, latitude: string | null, longitude: string | null, street: string, postcode: string, city: string, distance: number | null, email: string | null, phone: string | null, specialMessage: string | null, identifier: string, openingHours: { status: Types.TypeStoreOpeningStatusEnum, dayOfWeek: number, openingHoursOfDays: Array<{ date: string, dayOfWeek: number, openingHoursRanges: Array<{ openingTime: string, closingTime: string }> }> }, country: { __typename: 'Country', name: string, code: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null } | null } | null> | null } | null, productsBlockingSelectionInCart: Array<{ reason: Types.TypeTransportUnavailabilityReasonInCartEnum, products: Array<
            | { uuid: string, fullName: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
            | { uuid: string, fullName: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
            | { uuid: string, fullName: string, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
          > }> } | null, payment: { __typename: 'Payment', uuid: string, name: string, description: string | null, instructions: string | null, type: Types.TypePaymentTypeEnum, price: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, mainImage: { __typename: 'Image', name: string | null, url: string } | null, goPayPaymentMethod: { __typename: 'GoPayPaymentMethod', identifier: string, name: string, paymentGroup: string } | null } | null, promoCodes: Array<{ __typename: 'PromoCode', code: string, type: Types.TypePromoCodeTypeEnum, discountPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, roundingPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } | null } | null } };


export const CreateOrderMutationDocument = gql`
    mutation CreateOrderMutation($firstName: String!, $lastName: String!, $email: String!, $telephone: PhoneDataInput!, $onCompanyBehalf: Boolean!, $companyName: String, $companyNumber: String, $companyTaxNumber: String, $street: String!, $city: String!, $postcode: String!, $country: String!, $isDeliveryAddressDifferentFromBilling: Boolean!, $deliveryFirstName: String, $deliveryLastName: String, $deliveryCompanyName: String, $deliveryTelephone: PhoneDataInput, $deliveryStreet: String, $deliveryCity: String, $deliveryPostcode: String, $deliveryCountry: String, $deliveryAddressUuid: Uuid, $note: String, $cartUuid: Uuid, $newsletterSubscription: Boolean, $heurekaAgreement: Boolean!) {
  CreateOrder(
    input: {firstName: $firstName, lastName: $lastName, email: $email, telephone: $telephone, onCompanyBehalf: $onCompanyBehalf, companyName: $companyName, companyNumber: $companyNumber, companyTaxNumber: $companyTaxNumber, street: $street, city: $city, postcode: $postcode, country: $country, isDeliveryAddressDifferentFromBilling: $isDeliveryAddressDifferentFromBilling, deliveryFirstName: $deliveryFirstName, deliveryLastName: $deliveryLastName, deliveryCompanyName: $deliveryCompanyName, deliveryTelephone: $deliveryTelephone, deliveryStreet: $deliveryStreet, deliveryCity: $deliveryCity, deliveryPostcode: $deliveryPostcode, deliveryCountry: $deliveryCountry, deliveryAddressUuid: $deliveryAddressUuid, note: $note, heurekaAgreement: $heurekaAgreement, cartUuid: $cartUuid, newsletterSubscription: $newsletterSubscription}
  ) {
    orderCreated
    order {
      number
      uuid
      urlHash
      items {
        type
        payment {
          type
        }
      }
    }
    cart {
      ...CartFragment
    }
  }
}
    ${CartFragment}`;

export function useCreateOrderMutation() {
  return Urql.useMutation<TypeCreateOrderMutation, TypeCreateOrderMutationVariables>(CreateOrderMutationDocument);
};