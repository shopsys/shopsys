// @ts-nocheck
/** Internal type. DO NOT USE DIRECTLY. */
type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
/** Internal type. DO NOT USE DIRECTLY. */
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
import * as Types from '../../../types';

import gql from 'graphql-tag';
import { CreateComplaintFragment } from '../fragments/CreateComplaintFragment.generated';
import * as Urql from 'urql';
export type Omit<T, K extends keyof T> = Pick<T, Exclude<keyof T, K>>;
/** Product Availability statuses */
export type TypeAvailabilityStatusEnum =
  /** Product is out of stock with a known expected restocking date */
  | 'ExpectedRestock'
  /** Product availability status in stock */
  | 'InStock'
  /** Product availability status out of stock */
  | 'OutOfStock';

export type TypeComplaintInput = {
  /** Bank account number for money return */
  bankAccountNumber?: string | null | undefined;
  /** Delivery address */
  deliveryAddress: TypeDeliveryAddressInput;
  /** The customer's email address */
  email: string;
  /** All items in the complaint */
  items: Array<TypeComplaintItemInput>;
  /** Order or document number (doesn't have to be from any existing order) */
  manualDocumentNumber?: string | null | undefined;
  /** UUID of the order */
  orderUuid?: string | null | undefined;
  /** Chosen resolution from complaintResolutionQuery */
  resolution: string;
};

export type TypeComplaintItemInput = {
  /** Description of the complaint item */
  description: string;
  /** Files attached to the complaint item */
  files?: Array<File> | null | undefined;
  /** Catalog number of the complaint item entered by customer (if the complaint is created without an order, otherwise, the catalog number is taken from the order item) */
  manualComplaintItemCatnum?: string | null | undefined;
  /** Name of the complaint item entered by customer (if the complaint is created without an order, otherwise, the name is taken from the order item) */
  manualComplaintItemName?: string | null | undefined;
  /** UUID of the order item */
  orderItemUuid?: string | null | undefined;
  /** Quantity of the complaint item */
  quantity: number;
};

export type TypeDeliveryAddressInput = {
  /** Delivery address city name */
  city: string;
  /** Delivery address company name */
  companyName?: string | null | undefined;
  /** Delivery address country */
  country: string;
  /** Delivery address first name */
  firstName: string;
  /** Delivery address last name */
  lastName: string;
  /** Delivery address zip code */
  postcode: string;
  /** Delivery address street name */
  street: string;
  /** Delivery address telephone */
  telephone?: TypePhoneDataInput | null | undefined;
  /** UUID */
  uuid?: string | null | undefined;
};

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

/** Represents phone number input */
export type TypePhoneDataInput = {
  /** Phone prefix country code in ISO 3166-1 alpha-2 */
  countryCode: string;
  /** Phone number without prefix */
  number: string;
  /** Phone prefix (eg. +420) */
  prefix: string;
};

/** One of the possible methods of the transport type */
export type TypeTransportTypeEnum =
  | 'common'
  | 'packetery'
  | 'personal_pickup';

export type TypeCreateComplaintVariables = Exact<{
  input: Types.TypeComplaintInput;
}>;


export type TypeCreateComplaint = { CreateComplaint: { uuid: string, number: string, deliveryFirstName: string, deliveryLastName: string, deliveryCompanyName: string | null, deliveryTelephone: string, deliveryStreet: string, deliveryCity: string, deliveryPostcode: string, createdAt: string, bankAccountNumber: string | null, deliveryCountry: { __typename: 'Country', name: string, code: string }, items: Array<{ uuid: string, quantity: number, description: string, catnum: string | null, productName: string, orderItem: { __typename: 'OrderItem', uuid: string, name: string, vatRate: string, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, catnum: string | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, relatedItems: Array<{ __typename: 'OrderItem', uuid: string, name: string, catnum: string | null, quantity: number, unit: string | null, type: Types.TypeOrderItemTypeEnum, deliveryDaysExtension: number | null, mainImage: { __typename: 'Image', name: string | null, url: string } | null, unitPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string }, totalPrice: { __typename: 'Price', priceWithVat: string, priceWithoutVat: string, vatAmount: string } }>, order: { uuid: string, number: string, creationDate: string, customerUser:
            | { uuid: string }
            | { uuid: string }
            | { uuid: string }
            | { uuid: string }
           | null, withdrawalRequest: { __typename: 'OrderWithdrawalRequest' } | null }, product:
          | { uuid: string, catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { name: string, status: Types.TypeAvailabilityStatusEnum } }
          | { uuid: string, catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { name: string, status: Types.TypeAvailabilityStatusEnum } }
          | { uuid: string, catalogNumber: string, slug: string, isVisible: boolean, isSellingDenied: boolean, isInquiryType: boolean, isCurrentlyOutOfStock: boolean, promotionBuyQuantity: number | null, promotionFreeQuantity: number | null, categories: Array<{ name: string }>, mainImage: { __typename: 'Image', name: string | null, url: string } | null, price: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, giftPrice: { __typename: 'ProductPrice', priceWithVat: string, priceWithoutVat: string, vatAmount: string, isPriceFrom: boolean, nextPriceChange: string | null, percentageDiscount: number | null, basicPrice: { priceWithVat: string, priceWithoutVat: string, vatAmount: string } }, availability: { name: string, status: Types.TypeAvailabilityStatusEnum } }
         | null, transport: { transportTypeCode: Types.TypeTransportTypeEnum, mainImage: { url: string } | null } | null, payment: { mainImage: { url: string } | null } | null } | null, files: Array<{ __typename: 'File', anchorText: string, url: string, viewUrl: string | null, filesize: number | null, extension: string | null }> | null, product:
        | { slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
        | { slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
        | { slug: string, isVisible: boolean, mainImage: { __typename: 'Image', name: string | null, url: string } | null }
       | null }>, resolution: { name: string, value: string } } };


export const CreateComplaintDocument = gql`
    mutation CreateComplaint($input: ComplaintInput!) {
  CreateComplaint(input: $input) {
    ...CreateComplaintFragment
  }
}
    ${CreateComplaintFragment}`;

export function useCreateComplaint() {
  return Urql.useMutation<TypeCreateComplaint, TypeCreateComplaintVariables>(CreateComplaintDocument);
};